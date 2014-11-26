#! /bin/env python
# -*- coding: utf-8 -*-
# download webpage using wget 
# add tcpdump into it
import os, MySQLdb
import string
import time
pm1=MySQLdb.connect(host='127.0.0.1',user='root',db='mnt')
cur1=pm1.cursor()

name=str(time.time())
filepath='./'+name
success = 0
source = ['website1', 'website2','endpoint']
cmd_list = []
cmd_list.append('wget -vt 1 -T 10 --delete-after -o '+filepath+'.txt -O '+filepath+'wget.html http://hpc.cs.tsinghua.edu.cn/research/cluster/papers_cwg/phantom.pdf')
cmd_list.append('wget -vt 1 -T 10 --delete-after -o '+filepath+'.txt -O '+filepath+'wget.html http://cnais.sem.tsinghua.edu.cn/events/258.pdf')
cmd_list.append('wget -vt 1 -T 10 --delete-after -o '+filepath+'.txt -O '+filepath+'wget.html http://166.111.132.80:52206/001.mp3')
for id, cmd in zip(source, cmd_list):
	success = 0
	print cmd,'\n'
	a=os.popen(cmd).read()
	file=open(filepath+'.txt','r')
	a=file.read()
	file.close()
	for i in a.split('\n'):
		if(i.count(' saved [')):
			a1=i.split(' saved [')
			#print 'a1= ',a1,'\n'
			a2=a1[1].split(']')
			#print 'a2= ',a2,'\n'
			if(a2[0].count('/')):
				a7=a2[0].split('/')
				a2[0]=a7[0]
			pagesize=int(a2[0])
			a3=a1[0].split('\'')
			#print 'a3= ',a3,'\n'
			a4=a3[len(a3)-2].split('`')
			#print 'a4= ',a4,'\n'
		
			a5=a4[0].split('(')
			#print 'a5= ',a5,'\n'
			a6=a5[1].split(')')
			#print 'a6= ',a6,'\n'
			if(a6[0].count('MB/s')):
				a8=a6[0].split(' ')
				bandwidth=float(a8[0])*1000000
			elif(a6[0].count('KB/s')):
				a8=a6[0].split(' ')
				bandwidth=float(a8[0])*1000
			else:
				a8=a6[0].split(' ')
				bandwidth=float(a8[0])
			ilist = i.split(' ')
			print ilist
			i = i.replace("'","")
			i = i.replace("`","")
			sql = "insert into d80 values('wget-{2}', {0}, now(), \"{1}\")".format(bandwidth, i, id)
			print sql
			cur1.execute(sql)
			success = 1
			break
	if not success:
		cur1.execute("insert into d80 values('wget-{0}', 0, now(), 'failed')".format(id) )

cmd='rm '+filepath+'*'
a=os.popen(cmd).read()

