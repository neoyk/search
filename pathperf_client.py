#! /bin/env python
# -*- coding: utf-8 -*-
# Beief documentation:
# Pathperf client, estimate path BTC from vantage website to this client
# Written and tested under Fedora11 with Python 2.6
# parameter list:
# [-t1/--thread=1] [-i any/--interface=any] [-b/--bypassCDN] [-c/--crawl] [-q/---quota] [-v/--verbose] [-h/--help] IPv4/IPv6_addr

#   Copyright [2012] [Kun Yu yukun2005@gmail.com]

#   Licensed under the Apache License, Version 2.0 (the "License");
#   you may not use this file except in compliance with the License.
#   You may obtain a copy of the License at

#       http://www.apache.org/licenses/LICENSE-2.0

#   Unless required by applicable law or agreed to in writing, software
#   distributed under the License is distributed on an "AS IS" BASIS,
#   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#   See the License for the specific language governing permissions and
#   limitations under the License.

import os,sys,string,re,threading,subprocess,math,time,socket,logging,getopt,glob,shlex
from urlparse import urlparse
from easyprocess import EasyProcess
import dns.resolver    #install dnspython first http://www.dnspython.org/

def usage():
    print "usage: python",sys.argv[0],"[-h/--help] [-v/--verbose] [-i any/--interface=any] [-b/bypassCDN] [-c/--crawl] [-q/---quota] [-t1/--thread=1] IP_address\n"
    print "Estimate BTC from vantage website to this client."
    print "Vantage website is decided by pathperf from any IP address in the same AS."
    print "For full description of pathperf, visit http://search.sasm3.net/documentation.html\n"
    print "-i: interface tcpdump listens to"
    print "-b: bypass Local DNS, use DNS provided by pathperf"
    print "-c: use wget as a simple crawler, using tcpdump to estimate bw at the same time"
    print "-q: set download quota for wget in crawler mode"
    print "-t: number of threads used to download webpage"
    print "eg:",sys.argv[0]," 166.111.1.1"
    print "or:",sys.argv[0],"-cvt1 -i eth0 166.111.1.1\n"

def dnslookup(querydomain,type='A'):
    type=type.upper()
    response=[]
    resolver = dns.resolver.Resolver()
    resolver.nameservers=[socket.gethostbyname('ip2.sasm4.net')]
    try:
        answer=resolver.query(querydomain,type)
    #except dns.exception.DNSException:
    except dns.resolver.NXDOMAIN:
        #Local DNS may return "NXDOMAIN" error, if this happens, pathperf queries authoritative DNS server for answer
        resolver.nameservers=[socket.gethostbyname('ip2.sasm4.net')]
        answer=resolver.query(querydomain,type)
    #dir(answer)
    if(type=='A' or type=='AAAA'):
        if( str(answer.response.answer[0][0])[-1]=='.'):    #CNAME
            response.append(str(answer.response.answer[0][0])[:-1])    #CNAME
        else:
            response.append(str(answer.response.answer[0][0]))    #CNAME
        response.append(str(answer.response.answer[1][0]))    #A/AAAA
        return response
    if(type=='TXT'):
        response.append(str(answer.response.answer[0][0]))    #ASN or URL
        return response

def ipv6exp(ip6addr):
    """ipv6 address expanding function

    replace :: in an IPv6address with zeros
    return the list after split(':')
    """
    ast2=ip6addr.count('::')
    if(ast2==0): return ip6addr.split(':')
    ast1=ip6addr.count(':')-2*ast2
    num=7-ast1
    i=1
    pad=':'
    while i<num:
        pad=pad+'0:'
        i=i+1
    ip6full=ip6addr.replace('::',pad)
    if ip6full[-1]==':':ip6full=ip6full+'0'
    if ip6full[0]==':':ip6full='0'+ip6full
    #print ip6full
    return ip6full.split(':')
    
class mea_thread(threading.Thread):
    def __init__(self, ip, domain, url, version, verbose, number, bypassCDN, crawl, quota):
        threading.Thread.__init__(self)
        self.ip=ip
        self.domain=domain
        self.url=url
        self.version=str(int(version))
        self.verbose=int(verbose)
        self.bypassCDN=int(bypassCDN)
        self.crawl=int(crawl)
        self.quota=int(quota)
        self.number=int(number)
    def run(self):
        global est_result
        realurl=self.url
        realip=self.ip
        realdomain=self.domain
        verbose=self.verbose
        number=self.number
        name=str(time.time())+'-'+str(number)
        filepath=path+os.path.sep+name
        if(self.crawl):
            cmd = "wget -r -p -e robots=off --delete-after -Q"+str(self.quota)+"m -U Mozilla "
        else:
            cmd = "wget "
        if(self.bypassCDN):
            #wget --header="Host: ak.buy.com" http://206.132.122.75/PI/0/500/207502093.jpg
            cmd += '--header="Host: '+self.domain+'" '
            cmd += '-'+self.version+' -T 10 -t 1 -o '+filepath+'.txt -O '+filepath+'.html http://'
            if(self.version=='6'):
                cmd=cmd+'['+self.ip+']'
            else:
                cmd=cmd+self.ip
        else:
            cmd += '-'+self.version+' -T 10 -t 1 -o '+filepath+'.txt -O '+filepath+'.html http://'\
            +self.domain
        if not self.crawl:
            cmd += self.url
        print cmd
        stdout=EasyProcess(cmd).call(timeout=15).stdout #timeout after 15s
        try:
        #print filepath
            file=open(filepath+'.txt','r')
            log=file.read()
            file.close()
        except :
            logging.error("Reading log failed.\nDownload command: %s",cmd)
            est_result=0
            exit()
        file_list=glob.glob(filepath+'*')
        for f in file_list:
            os.remove(f)
        if verbose:    print '-'*70,'\nParsing Wget log:\n'
        if(log.count(' saved [')==0):
            logging.error("Wget failed to download any files. Wget log:\n%s",log)
            est_result=2
            exit()
        loglist=log.split('\n')
        for linenum,logline in enumerate(loglist):
            #print logline
            if(logline.count('Location: ')):    #HTTP redirect
                logging.warning('HTTP redirection found.\nThe file may not come from\
 the vantage website designated by pathperf.\nThe result could be inaccuate.')
                if(loglist[linenum+1].count('Warning: ')):
                    nextline=loglist[linenum+2]
                else:
                    nextline=loglist[linenum+1]
                nextlist=nextline.split('  ')
                realurl=nextlist[1]
                try:
                    urlist=urlparse(nextlist[1])
                    realurl=urlist.path
                    logging.warning('Actual URL: %s',nextlist[1])
                except:
                    logging.error("URL extraction error. Wget log:\n%s",logline)
            elif(logline.count('Connecting to ') and logline.count('connected')):
                #print 'con****, ',logline
                if(logline.count(self.ip)==0):
                    pattern=re.compile('Connecting to (\S+).*\|(\S+)\|\S+.+connected\.')
                    reout=pattern.search(logline)
                    if reout:
                        realdomain=reout.group(1)
                        realip=reout.group(2)
                    logging.error('Connection info: %s',logline)
            elif(logline.count('saved [')):
                #print 'save***, ',logline
                a1=logline.split(' saved [')
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
                    literal=float(a8[0])*1024*1024
                elif(a6[0].count('KB/s')):
                    a8=a6[0].split(' ')
                    literal=float(a8[0])*1024
                else:
                    a8=a6[0].split(' ')
                    literal=float(a8[0])
                global total_size,max_time
                total_size+=int(pagesize)
                down_time=int(pagesize)/literal
                if max_time<down_time:    max_time=down_time
                bandwidth=a6[0]
                #print ip4[0],'\n',ipnum,'\n',tmp1,'\n',directory,'\n',asn,'\n',pagesize,'\n',bandwidth
                break
            else:
                continue
        if self.verbose:
            print "{0}".format(log)
            print '-'*70
        print "Input website: {0}|{1}|{2}".format(self.domain,self.ip,self.url)
        print "Download from: {0}|{1}|{2}".format(realdomain,realip,realurl)
        print "Download size: {0}, Bulk Throughput Capacity: {1}".format(pagesize,bandwidth)
        '''
        if(self.ip==realip):
            print "Estimation succeeds!"
        else:
            print "Estimation fails! Add '-b' and try again."
            est_result=0
        '''
start=time.time()
path= '.'
'''
#test code
estimation=mea_thread('206.132.122.75','ak.buy.com','/db_assets/large_images/093/207502093.jpg',4)    #ip,domain,url,version
estimation.start()
while estimation.isAlive():
    time.sleep(0.5)
exit()
'''
if __name__=='__main__':
    try:
        opts,args = getopt.gnu_getopt(sys.argv[1:],"hvt:i:bcq:",["help", "verbose", "thread=", "interface=", "bypassCDN", "crawl", "quota="])
    except getopt.GetoptError as err:
        print "\n",str(err),"\n"
        usage()
        sys.exit(2)
    thread = 1
    multi_speed = 0
    total_size = 0
    max_time = 0
    verbose = 0
    bypassCDN = 0
    crawl = 0
    quota = 2
    interface='any'
    ipaddr='166.111.1.1'    #default IP address belongs to AS4538
    for o, a in opts:
        if     o in ("-h", "--help"):
            usage()
            sys.exit()
        elif o in ("-v", "--verbose"):
            verbose = 1
        elif o in ("-t", "--thread"):
            thread = int(a)
        elif o in ("-i", "--interface"):
            interface = a
        elif o in ("-b", "--bypassCDN"):
            bypassCDN = 1
        elif o in ("-c", "--crawl"):
            crawl = 1
            print "Crawl mode"
        elif o in ("-q", "--quota"):
            try:
                quota = int(a)
            except:
                quota = 2
            if quota>10: quota = 10
        else:
            assert False, "unhandled option"
            
    if args:
        ipaddr=args[0]
        try:
            socket.inet_pton(socket.AF_INET6,ipaddr)
            ipv=6
        except:
            try:
                socket.inet_pton(socket.AF_INET,ipaddr)
                ipv=4
            except:
                logging.error("Invaild IP address.")
                sys.exit(1)
    else:
        logging.error("IP address missing!")
        usage()
        sys.exit(2)

    if ipv==4:
        ip4list=ipaddr.split('.')
        ip4list.reverse()
        #IP address to AS number mapping
        #ip4asn='.'.join(ip4list)+'.ip2asn.sasm4.net'
        #print dnslookup(ip4asn,'txt')
        ip4ser='.'.join(ip4list)+'.ip2server.sasm4.net'
        arg1=dnslookup(ip4ser,'a')
        ip4url='.'.join(ip4list)+'.ip2url.sasm4.net'
        arg2=dnslookup(ip4url,'txt')
    else:
        ip6list=ipv6exp(ipaddr)
        ip6list.reverse()
        #IP address to AS number mapping
        #ip6asn='.'.join(ip6list)+'.ip6asn.sasm4.net'
        #print dnslookup(ip6asn,'txt')
        ip6ser='.'.join(ip6list[4:])+'.ip6server.sasm4.net'
        arg1=dnslookup(ip6ser,'aaaa')
        ip6url='.'.join(ip6list[4:])+'.ip6url.sasm4.net'
        arg2=dnslookup(ip6url,'txt')

    if arg1[0] in ('Error.','No-IP-Record.','No-Web-Server-in-that-AS.', 'nowebsite.wind.sasm4.net.'):
        logging.error("Vantage website localization failed: %s",arg1[0])
        sys.exit(3)
    thr_list=[]
    cmd1 = ['tcpdump', '-i', interface, '-tt','-nn', 'src', arg1[1]]
    #print cmd1
    #starts tcpdump to capture packets
    print '-'*70
    est_result=1

    filepath=path+os.path.sep+str(time.time())+'.'+'tcpdump.log'
    file_handle=open(filepath,'w')
    p1 = subprocess.Popen(cmd1, stdout=file_handle, stderr=file_handle)
    for i in range(thread):
        thr_list.append(mea_thread(arg1[1],arg1[0],arg2[0].strip('"'), ipv, verbose, i, bypassCDN, crawl, quota) )    #ip,domain,url,version,verbose,num
    for estimation in thr_list:
        estimation.start()
    for estimation in thr_list:
        while estimation.isAlive():
            time.sleep(0.5)
    end=time.time()

    p1.terminate()    #stops tcpdump
    file_handle.flush()
    file_handle.close()
    print '-'*70,'\n'
    if(est_result):
        search=re.compile('^(\d*\.\d*).*\((\d+)\).*')
        #search=re.compile('^(\d*\.\d*).*length\s(\d+)$')
        #tcpdump for linux and Mac have different output format
        time_list=[];size_list=[]
        #for line in iter(p1.stdout.readline, ""):
        for line in open(filepath,'r'):
            out=search.search(line)
            if out:
                time_list.append(float(out.group(1)))
                size_list.append(int(out.group(2)))
                #print out.group(1),out.group(2)
        #print time_list
        os.remove(filepath)
        down_size=sum(size_list)
        if(len(time_list)):
            time_total=time_list[-1]-time_list[0]
            if time_total>4:
                part=int(time_total)
            else:
                part=2
            size=int(len(time_list)/part)
            bw=[]    
            for i in range(part):
                data_part=sum(size_list[i*size:(i+1)*size])
                time_part=time_list[(i+1)*size-1]-time_list[i*size]
                if(time_part<0.2):
                    continue
                bw.append(data_part/time_part)
            max_bw=max(bw)
            if max_bw>1024*1024:
                max_bw/=(1024*1024)
                unit='MB/s'
            elif max_bw>1024:
                max_bw/=1024
                unit='KB/s'
            else:
                unit='B/s'
            print 'tcpdump statistics: download size',down_size,'B, Peak BTC:',round(max_bw),unit
            print '-'*70,'\n'
    print "Time used: {0} s".format(round(end-start,2))
