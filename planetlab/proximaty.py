#! /usr/bin/python
import urllib2, os, sys, subprocess, shlex, re, time, socket
#vantage website RTT test
request = urllib2.urlopen("http://115.25.86.11/ping.php")
ip = request.read()
realip, vantage_ip, vantage_domain = ip.split('|')
vantage_domain = vantage_domain.strip()
cmd = 'hping3 -S -p 80 -c 5 '+vantage_ip
pattern =re.compile(r"^.*flags=SA.*rtt=(.*)\sms$")
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
	avg = sum(rtt)/len(rtt)
	for i in rtt:
		string += str(i)+'|'
	string += out.strip()
	request = urllib2.urlopen("http://115.25.86.11/pong.php?ip={0}&vantage={1}&avg={2}&list={3}".format(realip, vantage_ip, avg, urllib2.quote(string) ) )
	request.read()
#DNS RTT test
fh = open('/etc/resolv.conf')
pattern_dns =re.compile(r"^nameserver\s(.*)$")
for msg in fh:
	result_dns = pattern_dns.search(msg)
	if result_dns:
		cmd = 'hping3 -S -p 53 -c 5 '+result_dns.group(1)
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
			avg = sum(rtt)/len(rtt)
			for i in rtt:
				string += str(i)+'|'
			string += out.strip()
			request = urllib2.urlopen("http://115.25.86.11/pong.php?ip={0}&dnsip={1}&avg={2}&list={3}".format(realip, result_dns.group(1), avg, urllib2.quote(string) ) )
			request.read()
			break
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
		request = urllib2.urlopen("http://115.25.86.11/pong.php?ip={0}&vantage_domain={1}&bw={2}&msg={3}".format(realip, urllib2.quote(vantage_domain), bw, urllib2.quote(line) ) )
		request.read()
		break
