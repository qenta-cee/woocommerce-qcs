#!/bin/bash

set -e

NGROK_BINARY="./node_modules/ngrok/bin/ngrok"

if [[ ! -x ${NGROK_BINARY} ]]; then
  >&2 echo "Installing NGROK"
  npm install ngrok &>/dev/null
fi

function get_ngrok_url() {
  curl --fail -s localhost:4040/api/tunnels | jq -r .tunnels\[0\].public_url | sed 's/^http:/https:/'
}

function wait_for_ngrok() {
  while [[ -z ${RESPONSE} || ${RESPONSE} == 'null' ]]; do
    RESPONSE=$(get_ngrok_url)
    sleep 3;
  done
}

[[ ${1} ]] && NGROK_TOKEN=${1}

if [[ -z ${NGROK_TOKEN} ]]; then
  echo 'NGROK token missing. Set NGROK_TOKEN env' >&2
  exit 1
fi

${NGROK_BINARY} authtoken ${NGROK_TOKEN} >&/dev/null
${NGROK_BINARY} http https://localhost:443 >&/dev/null &
wait_for_ngrok
export NGROK_URL=$(get_ngrok_url)
export NGROK_HOST=$(sed 's,^https\?://,,' <<< ${NGROK_URL})
echo ${NGROK_HOST}
