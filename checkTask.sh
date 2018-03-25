#!/bin/bash
# 查找文件所在根目录下有无shell/start_handle.sh有则执行

list=$(find / -name 'start_handle.sh'|grep 'shell/start_handle.sh');
for i in $list;
do
  has=$(ps aux|grep start_handle.sh |grep -v grep | awk  '{print $12}' |grep ${i});
  if [ ! -n $has ]; then
    j=${i/shell\/start_handle.sh/}
    nohup /bin/bash $i $j &
  fi
done