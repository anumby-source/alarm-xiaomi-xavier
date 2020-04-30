docker run \
   -d \
   -it \
   -v $(pwd)/data:/app/data \
   --device=/dev/ttyACM0 \
   -e TZ=Europe/Amsterdam \
   -v /run/udev:/run/udev:ro \
   --privileged=true \
--network host \
   koenkk/zigbee2mqtt
