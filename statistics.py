#! /usr/bin/python
# -*- coding: utf-8 -*-
import time, sys, MySQLdb, os, warnings
from datetime import datetime, timedelta
from collections import defaultdict

pm1=MySQLdb.connect(host='localhost',user='root',db='mnt')
cur1=pm1.cursor()
duration = 7 # a week
interval = int(sys.argv[2]) # aliyun/tianyi: 2
version = int(sys.argv[1])
if version == 4:
    offset = 7
else:
    offset = 3
threshold = -5
pagesize = 1e7
for delta in range(duration*24/interval):
    cmd = "select concat(substring(time,1,13),':00'),maxbw,latency,lossrate from web_perf{0} where time<= SUBDATE(curdate(), interval {2} hour) and time>= SUBDATE(curdate(), interval {3} hour) and id in (select id from ipv{0}server where crawl>={1} and error>{1} and slow>{1}) and maxbw>0 order by maxbw limit {4},800".format(version, threshold, duration*24 - (1+delta)*interval, duration*24-delta*interval, offset)
    cur1.execute(cmd)
    result = cur1.fetchall()
    print result[-1][0], len(result),sum([pagesize/0.9/i[1] for i in result])/ len(result)*300, sum([i[2] for i in result])/ len(result), sum([i[3] for i in result])/ len(result)
