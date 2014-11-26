#! /usr/bin/python

import os, sys, time
import MySQLdb, numpy
from scipy.cluster.vq import kmeans, vq

k = int(sys.argv[1])
prelatency = [ float(i) for i in sys.argv[2].split('-')]

latency = numpy.array(prelatency)
codebook, distor = kmeans(latency, k, 5, 10)
cluster_indices, _ = vq(latency, codebook)
codebook = [round(a,1) for a in codebook]
postlatency = [ codebook[cluster_indices[prelatency.index(a)]] for a in prelatency]
for i in postlatency:
		print i

