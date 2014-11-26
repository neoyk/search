#!/usr/bin/python
# -*- coding: utf-8 -*-
# File: pywget.py
# Date: 2010-04-12

import os, string, time, sys, re

name=sys.argv[1]	#name of wget log
#print name

filepath='/tmp/'+name+'.txt'

file=open(filepath,'r')
log = file.read()
file.close()
a = log.split('\n')
a=a[len(a)-3]
if(a.count(' saved [')):
	m = re.search("Connecting to [^\|]+\|([^|]+).* connected.",log)
	ip = m.group(1)
	a1=a.split(' saved [')
	a2=a1[1].split(']')
	if(a2[0].count('/')):
		a7=a2[0].split('/')
		a2[0]=a7[0]
	pagesize=int(a2[0])
	a3=a1[0].split('\'')
	a4=a3[len(a3)-2].split('`')
	
	a5=a4[0].split('(')
	a6=a5[1].split(')')
	if(a6[0].count('MB/s')):
		a8=a6[0].split(' ')
		bandwidth=float(a8[0])*1000000
	elif(a6[0].count('KB/s')):
		a8=a6[0].split(' ')
		bandwidth=float(a8[0])*1000
	else:
		a8=a6[0].split(' ')
		bandwidth=float(a8[0])

	print pagesize,'\n',bandwidth,'\n',ip
else:
	print 0,'\n',0,'\n',0

