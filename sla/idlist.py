import os
import string
import threading
import linecache
import time
import sys
import re
import httplib
import MySQLdb
pm1=MySQLdb.connect(host='localhost',user='root',db='mnt', charset = "utf8", use_unicode = True)
cur1=pm1.cursor()
cur1.execute("select id, avg(pagesize) from web_perf where time >'20130818' group by id having avg(pagesize) > 50000")
result = cur1.fetchall()
for i in result:
	print i[0],int(i[1])
