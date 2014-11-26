#! /usr/bin/env python

import urllib
import os

path=os.getcwd()
for i in range (1,809):
	webfile = urllib.urlopen("http://wszw.hzs.mofcom.gov.cn/fecp/fem/corp/fem_cert_stat_view_list.jsp?Grid1toPageNo="+str(i)).read()
	fp = file(path+'/web'+str(i)+'.txt', 'w')
	fp.write(webfile)
	fp.close()
