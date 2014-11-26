#! /usr/bin/python
import urllib2, math, os, sys, subprocess, shlex, re, time, socket
#vantage website RTT test
request = urllib2.urlopen("http://115.25.86.11/ping.php")
ip = request.read()
realip, vantage_ip, vantage_domain = ip.split('|')
vantage_domain = vantage_domain.strip()
if (int(sys.argv[1])==1 or int(sys.argv[1])==3):
	cmd = 'ping -c 5 '+vantage_ip
	#64 bytes from 145.100.53.13: icmp_seq=2 ttl=49 time=13.4 ms
	pattern =re.compile(r"^.*time=(.*)\sms$")
	rtt = []
	out = ''
	subp = subprocess.Popen(shlex.split(cmd),stdout=subprocess.PIPE,stderr=subprocess.PIPE)
	for msg in subp.stdout:
		result = pattern.search(msg)
		if result:
			out = msg
			rtt.append(float(result.group(1)) )
	string = ''
	if len(rtt):
		rtt.sort()
		idx =int( math.floor(len(rtt)/2) )
		avg = rtt[idx]
		for i in rtt:
			string += str(i)+'|'
		string += out.strip()
		string = urllib2.quote(string)
		request = urllib2.urlopen("http://115.25.86.11/pong.php?ip=%(realip)s&vantage=%(vantage_ip)s&avg=%(avg)f&list=%(string)s" %  vars() )
		request.read()
		print "Node:",realip,"RTT test:",string
	#DNS RTT test
	fh = open('/etc/resolv.conf')
	pattern_dns =re.compile(r"^nameserver\s(.*)$")
	for msg in fh:
		result_dns = pattern_dns.search(msg)
		if result_dns:
			cmd = 'ping -c 5 '+result_dns.group(1)
			rtt = []
			out = ''
			subp = subprocess.Popen(shlex.split(cmd),stdout=subprocess.PIPE,stderr=subprocess.PIPE)
			for msg in subp.stdout:
				result = pattern.search(msg)
				if result:
					out = msg
					rtt.append(float(result.group(1)) )
			string = ''
			if len(rtt):
				rtt.sort()
				idx =int( math.floor(len(rtt)/2) )
				avg = rtt[idx]
				for i in rtt:
					string += str(i)+'|'
				string += out.strip()
				request = urllib2.urlopen("http://115.25.86.11/pong.php?ip=%s&dnsip=%s&avg=%f&list=%s" % (realip, result_dns.group(1), avg, urllib2.quote(string) ) )
				request.read()
				print "Node:",realip,"DNS test:",string
				break
				
if (int(sys.argv[1])==2 or int(sys.argv[1])==3):
#vantage bw test
	head, tail = os.path.split(os.path.abspath(sys.argv[0]))
	filename = head+'/wget-'+str(time.time())+'.txt'
	cmd = 'wget -r -p -Q5m --delete-after -o '+filename+' http://'+vantage_domain
	head, tail = os.path.split(os.path.abspath(sys.argv[0]))
	filename = head+'/wget-'+str(time.time())+'.txt'
	cmd = 'wget -r -p -Q5m --delete-after -o '+filename+' http://'+vantage_domain

	#Downloaded: 138 files, 32M in 0.5s (60.3 MB/s)
	pattern =re.compile(r"^Downloaded:.*in.*\((.*)\s(.*)\)")
	subp = subprocess.Popen(shlex.split(cmd),stdout=subprocess.PIPE,stderr=None)
	subp.communicate()
	fd = open(filename)
	log = fd.read()
	fd.close()
	for line in log.split('\n'):

		result = pattern.search(line)
		if result:
			#rtt.append(float(result.group(1)) )
			if(result.group(2)=='MB/s'):
				bw=float(result.group(1))* 1000000
			elif(result.group(2)=='KB/s'):
				bw=float(result.group(1))* 1000
			else:
				bw=float(result.group(1))
			#print line, bw
			request = urllib2.urlopen("http://115.25.86.11/pong.php?ip=%s&vantage_domain=%s&bw=%f&msg=%s" % (realip, urllib2.quote(vantage_domain), bw, urllib2.quote(line) ) )
			request.read()
			print "Node:",realip,"BW test:",line
			break
print '\n'
