#!/bin/bash
nice --adjustment=1 ffmpeg -y -i $1 -vcodec flv -qscale 5 -f flv -ar 11025 -ac 2 -ab 32k -r 15 -y -s 450x360 $2
if [ $? -eq 0 ]         # Test exit status of "ffmpeg" command.
then
# generate video preview
  FILE=$2
  ffmpeg -v -y -i $1 -f mjpeg -ss 2 -vframes 1 -s 100x76 -an ${FILE%%.*}.jpg
  ffmpeg -v -y -i $1 -f mjpeg -ss 2 -vframes 1 -s 474x320 -an ${FILE%%.*}_preview.jpg
  exit $?
else 
  exit 1  
fi
    

