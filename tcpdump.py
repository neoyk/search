#! /bin/env python

import MySQLdb, os,sys,string,re,threading,subprocess,math,time,socket,logging,getopt,glob,shlex

pm1=MySQLdb.connect(host='127.0.0.1',user='root',db='mnt')
cur1=pm1.cursor()


if __name__=='__main__':
	#cmd1 = ['tcpdump', '-i', 'eth1', '-tt','-nn', 'src', '166.111.132.80']
	cmd1 = ['/usr/sbin/tcpdump', '-i', 'eth1', '-tt','-nn', 'src', sys.argv[1]]
	#print cmd1
	#starts tcpdump to capture packets

	filepath='./tcpdump'
	file_handle=open(filepath,'w')
	p1 = subprocess.Popen(cmd1, stdout=file_handle, stderr=file_handle)
	time.sleep(8.1)
	est_result = 1
	p1.terminate()	#stops tcpdump
	file_handle.flush()
	file_handle.close()
	if(est_result):
		search=re.compile('^(\d*\.\d*).*\((\d+)\).*')
		time_list=[];size_list=[]
		#for line in iter(p1.stdout.readline, ""):
		for line in open(filepath,'r'):
			out=search.search(line)
			if out:
				time_list.append(float(out.group(1)))
				size_list.append(int(out.group(2)))
				#print out.group(1),out.group(2)
		#print time_list
		##os.remove(filepath)
		down_size=sum(size_list)
		if(len(time_list)):
			time_total=time_list[-1]-time_list[0]
			part=4
			size=int(len(time_list)/part)
			bw=[]	
			for i in range(part):
				data_part=sum(size_list[i*size:(i+1)*size])
				time_part=time_list[(i+1)*size-1]-time_list[i*size]
				if(time_part ):
					bw.append(int(data_part/time_part))
			print bw
			string = ''
			for i in bw:
				string += str(i)+'|'
			if string[-1]=='|':
				string = string[:-1]
			max_bw=max(bw)
			msg = 'download size {0} B, bw list {2}, Peak BTC: {1} B/s'.format(down_size, max_bw, string)
			cur1.execute("insert into d80 values('iperf-tcpdump', {0}, now(), \"{1}\")".format(max_bw, msg))
	else:
		print 'no data'
