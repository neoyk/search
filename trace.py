#! /bin/env python
# insert into traceroute values('2.2.2.',now(),'1.1.1.1 * 3.3.3.3 5.5.5.5','as1 * as3 as5');
# insert into trraw values('1.1.1.1',now(),' 1 * 2 * 3 * 4 * ')

#traceroute -An 166.111.132.1
#traceroute to 166.111.132.1 (166.111.132.1), 30 hops max, 60 byte packets
# 1  115.25.86.1 [AS4538]  0.667 ms  0.650 ms  0.669 ms
# 2  * * *
# 3  202.112.53.73 [AS4538/AS9800]  1.290 ms  1.169 ms  1.156 ms
# 4  202.112.53.170 [AS4538/AS9800]  0.423 ms  0.419 ms  0.405 ms
# 5  202.112.38.74 [AS4538/AS9800]  41.483 ms  41.479 ms  41.465 ms
# 6  118.229.4.2 [AS4538]  1.012 ms  1.191 ms  1.220 ms
# 7  59.66.2.65 [AS9800]  1.445 ms  1.498 ms  1.599 ms
# 8  118.229.2.13 [AS4538]  9.505 ms  9.655 ms  9.646 ms
# 9  118.229.2.9 [AS4538]  1.656 ms  1.652 ms  1.640 ms
#10  59.66.4.134 [AS9800]  1.483 ms  1.476 ms  1.877 ms
#11  166.111.128.34 [AS4538/AS9800]  1.671 ms * *

#traceroute -Tnp 80 -f3 www.yale.edu
#traceroute to www.yale.edu (130.132.35.53), 30 hops max, 60 byte packets
# 3  202.127.216.237  0.872 ms  0.864 ms  0.855 ms
# 4  202.112.61.158  0.341 ms  0.343 ms  0.335 ms
# 5  202.112.53.18  0.566 ms  0.565 ms  0.664 ms
# 6  210.25.189.65  3.266 ms  4.065 ms  5.131 ms
# 7  210.25.189.18  0.672 ms  0.671 ms  0.710 ms
# 8  210.25.189.50  151.406 ms  151.172 ms  151.144 ms
# 9  210.25.189.134  146.690 ms  146.618 ms  162.588 ms
#10  64.57.28.97  178.725 ms  178.650 ms  178.618 ms
#11  64.57.28.56  192.657 ms  192.656 ms  192.831 ms
#12  64.57.28.37  203.321 ms  203.246 ms  203.248 ms
#13  192.5.89.17  225.708 ms  274.721 ms  274.693 ms
#14  192.5.89.238  234.926 ms  234.896 ms  234.870 ms
#15  207.210.143.90  225.927 ms  225.802 ms  225.799 ms
#16  130.132.251.73  231.248 ms  231.244 ms  231.217 ms
#17  * * *
#18  130.132.35.53  231.778 ms  231.745 ms  231.401 ms

import os,sys,string,struct,threading,time, subprocess, shlex
import MySQLdb

if(len(sys.argv)<2):
    print "usage: ",sys.argv[0]," version"
    exit()
version = sys.argv[1]
if(version!='4' or version != '6'):
    version = '4'

pm=MySQLdb.connect(host='localhost',user='root',db='mnt',charset="utf8")
cur=pm.cursor()
cur.execute("set interactive_timeout=24*3600")

ISOTIMEFORMAT='%Y-%m-%d %X'
print "Start at: ",time.strftime(ISOTIMEFORMAT,time.localtime())

class mea_thread(threading.Thread):
    def __init__(self, iplist, version):        #x='1.2.3.4'
        threading.Thread.__init__(self)
        self.iplist = iplist
        self.version = version
        self.pm = MySQLdb.connect(host='localhost',user='root',db='mnt',charset="utf8")
        self.cur = self.pm.cursor()
    def run(self):
        print len(self.iplist),'in total.'
        count = 0
        for ip in self.iplist:
            if(self.version=='6'):
                cmd='traceroute6 -Tn -p 80 ' + str(ip)
            else:
                cmd='traceroute -Tn -p 80 ' + str(ip)
            print cmd
            p1 = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            p1.wait()
            log = p1.stdout.read()
            cmd="replace into trraw{2} values('{0}',now(),'{1}',null)".format(ip, log, self.version)
            self.cur.execute(cmd)
            count += 1
            if(count%10==0):
                print count,"IPs processed,",len(self.iplist)-count,"to go."
cnt=0
num=cnt+1
t1=[]
cur.execute("select distinct ip from web_perf{0} where ip not in (select distinct ip from trraw{0}) ".format(version))
iplist = [ i[0] for i in cur.fetchall()]
print "Database read complete at: ",time.strftime(ISOTIMEFORMAT,time.localtime())
r=mea_thread(iplist,version)
r.start()
r.join()
print "End at: ",time.strftime(ISOTIMEFORMAT,time.localtime())
