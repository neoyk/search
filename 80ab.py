#! /bin/env python
# -*- coding: utf-8 -*-
# download webpage using wget 
# add tcpdump into it
import os, MySQLdb, subprocess, shlex
import string, re, time
pm1=MySQLdb.connect(host='127.0.0.1',user='root',db='mnt')
cur1=pm1.cursor()

pattern = re.compile(r"^Transfer rate:\s+(.*)\s\[(.*)\]\sreceived")
source = ['website1', 'website2','endpoint']
cmd_list = []
cmd_list.append('/usr/bin/ab http://hpc.cs.tsinghua.edu.cn/research/cluster/papers_cwg/phantom.pdf')
cmd_list.append('/usr/bin/ab http://cnais.sem.tsinghua.edu.cn/events/258.pdf')
cmd_list.append('/usr/bin/ab http://166.111.132.80:52206/001.mp3')
for id, cmd in zip(source, cmd_list):
	print cmd,'\n'
	success = 0
	subp = subprocess.Popen(shlex.split(cmd),stdout=subprocess.PIPE,stderr=None)
	for msg in subp.stdout:
		result = pattern.search(msg)
		if(result):
			print result.group(0)
			print result.group(1)
			print result.group(2)
			if result.group(2) =='Kbytes/sec':
				bandwidth=float(result.group(1))*1000
			else:
				bandwidth=float(result.group(1))
				
			sql = "insert into d80 values('ab-{2}', {0}, now(), \"{1}\")".format(bandwidth, result.group(0), id)
			print sql
			cur1.execute(sql)
			success = 1
			break
	if not success:
		cur1.execute("insert into d80 values('ab-{0}', 0, now(), 'failed')".format(id) )

