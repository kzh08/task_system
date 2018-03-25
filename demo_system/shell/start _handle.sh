#!/bin/bash
#等待时间
wait_time=1

echo $$start

while true
do
    echo $(date '+%s') > /data/pid/$$

    nohup php ../cli.php v8ejrnh1289uvfg1e4kjda9f1u c1i StartHandle start >> log/start_handle.log 2>&1 &

    sleep ${wait_time}
done