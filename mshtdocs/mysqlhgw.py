#!/bin/env python
# -*- coding: utf-8 -*-
# File: mysqlhgw.py
# Date: 2011-5-27
# add hgw infomation to MySQL database
import os
import string
import linecache
import MySQLdb
import time
import IPy

path= os.path.abspath('.')
fullpath=path+"/subnethgw.txt"
file=open(fullpath,'r')

pm=MySQLdb.connect(host='localhost',user='root',db='video')
cur=pm.cursor()

for line in file:
	l1=line.split(' ')
	if(len(l1)!=3):break
	l2=filter(None, l1)
	user=l1[0]
	prefix=int(l1[1])
	hgw=l1[2].split('\n')
	hgw=hgw[0]

	ip6=IPy.IP(user)
	ipf=ip6.strFullsize(0)
	ipi=ipf.split(':')

	ipaddr0=int(ipi[0]+ipi[1],16)
	ipaddr1=int(ipi[2]+ipi[3],16)
	ipaddr2=int(ipi[4]+ipi[5],16)
	ipaddr3=int(ipi[6]+ipi[7],16)

	ip6=IPy.IP(hgw)
	ipf=ip6.strFullsize(0)

	print ipaddr0,' ',ipaddr1,' ',ipaddr2,' ',ipaddr3,' ',prefix,' ',hgw

	try:
		r=cur.execute("insert into hgw values (%s,%s,%s,%s,%s,%s)",(ipaddr0,ipaddr1,ipaddr2,ipaddr3,prefix,hgw))
	except:
		continue
file.close
