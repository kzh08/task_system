#!/bin/bash
#设置任务mq

#等待时间
wait_time=1

echo $$start

while true
do
    echo $(date '+%s') > /data/pid/$$

    php ../cli.php v8ejrnh1289uvfg1e4kjda9f1u v1 getTaskListFromDb setTaskMq

    sleep ${wait_time}
done