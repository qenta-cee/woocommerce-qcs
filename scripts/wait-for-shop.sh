#!/bin/bash

# entrypoint of shop now puts 'ready' in a file after installation of
# wordpress, woocommerce and plugin

function read_log() {
  docker exec -it woocommerce cat /tmp/shop.log
  #docker exec -it woocommerce "tail -f /path/to/file.log | sed '/^ready/ q'"
}

# LOG_CONTENT=$(read_log)
# echo "Waiting for Shop Setup to finish"
# while [[ -z $(read_log | grep ready) ]]; do
#   sleep 1;
# done

echo "Waiting for webserver"
while ! curl --fail -k https://localhost:443; do 
  sleep 10
done
