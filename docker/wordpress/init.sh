#!/bin/bash

set -e

# If we are in Github plugin repo CI environment
CI_REPO_URL=${GITHUB_SERVER_URL}/${GITHUB_REPOSITORY}
if [[ ${CI_REPO_URL} == ${PLUGIN_URL//.git/} ]]; then
  PLUGIN_VERSION=${GITHUB_SHA}
  CI='true'
fi

if [[ -z ${WORDPRESS_URL} && ! -e wp-config.php ]]; then
  echo "WORDPRESS_URL not specified."
  if [[ -n ${NGROK_TOKEN} ]]; then 
    echo "Launching ngrok to get temporary URL"
    WORDPRESS_URL=$(ngrok.sh ${NGROK_TOKEN})
  else
    echo "No NGROK_TOKEN specified. Using localhost as URL"
  fi
fi

echo "Waiting for DB host ${WORDPRESS_DB_HOST}"

while ! mysqladmin ping -h"${WORDPRESS_DB_HOST}" --silent; do
  sleep 1
done

function create_db() {
  echo "Creating Database"

  wp config create \
    --dbhost=${WORDPRESS_DB_HOST} \
    --dbname=${WORDPRESS_DB_NAME} \
    --dbuser=${WORDPRESS_DB_USER} \
    --dbpass=${WORDPRESS_DB_PASS} \
    --locale=${WORDPRESS_LOCALE}
}

function install_core() {
  echo "Installing Wordpress"

  wp core install \
    --url=${WORDPRESS_URL} \
    --title=${WORDPRESS_TITLE} \
    --admin_user=${WORDPRESS_ADMIN_USER} \
    --admin_password=${WORDPRESS_ADMIN_PASS} \
    --admin_email=${WORDPRESS_ADMIN_EMAIL} \
    --skip-email
}

function install_woocommerce() {
  echo "Installing WooCommerce"
  wp plugin install woocommerce --activate

  echo "Install Sample Data"
  wp plugin install wordpress-importer --activate
  wp import wp-content/plugins/woocommerce/sample-data/sample_products.xml --authors=create
}

function install_plugin() {
  STR_PLUGIN=$(get_plugin.sh ${PLUGIN_URL} ${PLUGIN_VERSION})
  PLUGIN_NAME=$(echo ${STR_PLUGIN} | cut -d'^' -f1)
  PATH_TO_ZIP=$(echo ${STR_PLUGIN} | cut -d'^' -f2)
  wp plugin install ${PATH_TO_ZIP} --activate  
}

function setup_store() {
  wp option set woocommerce_onboarding_opt_in "yes"
  wp option set woocommerce_onboarding_profile ""
  wp option set woocommerce_store_address "Store Street 11"
  wp option set woocommerce_store_address_2 ""
  wp option set woocommerce_store_city "Graz"
  wp option set woocommerce_store_postcode "8020"
  wp option set woocommerce_default_country "AT"
  wp wc --user=admin tool run install_pages
}

function print_info() {
  echo
  echo '####################################'
  echo
  echo "URL: https://${WORDPRESS_URL}"
  echo "Panel: https://${WORDPRESS_URL}/wp-admin/"
  echo "User: ${WORDPRESS_ADMIN_USER}"
  echo "Password: ${WORDPRESS_ADMIN_PASS}"
  echo
  echo '####################################'
  echo
}

if [[ -e wp-config.php ]]; then
  echo "Wordpress detected. Skipping installations"
  WORDPRESS_URL=$(wp option get siteurl | sed 's/http:/https:/')
else
  create_db
  install_core
  install_woocommerce
  setup_store
  if [[ -n ${PLUGIN_URL} ]]; then
    install_plugin
  fi
fi
if [[ -n ${CI} == 'true' ]]; then
  print_info
fi
apache2-foreground "$@"
