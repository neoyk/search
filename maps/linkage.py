#! /bin/env python
# -*- coding: utf-8 -*-
import os,math,time,sys,warnings
import MySQLdb
with warnings.catch_warnings():
    warnings.simplefilter("ignore")
    import scipy,pylab,fastcluster
    from scipy.cluster.hierarchy import dendrogram
def distance(lat1, long1, lat2, long2):
    """return distance between two coordinates
    (lat1,lon1) and (lat2,lon2) are two sets of longitudes and latitudes of two places
    unit: degree
    """
    # Convert latitude and longitude to 
    # spherical coordinates in radians.
    degrees_to_radians = math.pi/180.0
    R=6373	#earth radius
        
    # phi = 90 - latitude
    phi1 = (90.0 - lat1)*degrees_to_radians
    phi2 = (90.0 - lat2)*degrees_to_radians
        
    # theta = longitude
    theta1 = long1*degrees_to_radians
    theta2 = long2*degrees_to_radians
        
    # For two locations in spherical coordinates 
    # (1, theta, phi) and (1, theta, phi)
    # cosine( arc length ) = 
    #    sin phi sin phi' cos(theta-theta') + cos phi cos phi'
    # distance = rho * arc length
    
    cos = (math.sin(phi1)*math.sin(phi2)*math.cos(theta1 - theta2) + \
           math.cos(phi1)*math.cos(phi2))
    return round(math.acos(cos)*R,2)

if(len(sys.argv)>1):
    asn = sys.argv[1]
else:
    asn = 'as4538'
pm =MySQLdb.connect(host='localhost',user='root',db='webserver')
cur=pm.cursor()
tstart = time.time()
r=cur.execute("select latitude, longitude from ipv4vantage where asn='"+asn+"' and latitude is not null")
result = cur.fetchall()
#print "Reading database:",time.time()-tstart
tstart = time.time()
distance = scipy.spatial.distance.pdist(result,lambda u,v:distance(u[0],u[1],v[0],v[1]))
linkage = fastcluster.linkage(distance,method="average",metric=lambda u,v:distance(u[0],u[1],v[0],v[1]))
#print "Calculating clusters:",time.time()-tstart
'''
fig = pylab.figure(figsize=(8,8))
ax1 = fig.add_axes([0.1,0.1,0.8,0.8])
Z1 = dendrogram(linkage,truncate_mode='lastp' )
ax1.set_xticks([])
ax1.set_yticks([])
fig.savefig('dendrogram.png')
'''
clustdict = dict((i,[i]) for i in xrange(len(linkage)+1))
for j in xrange(len(linkage)):
    #TODO tradeoff how to choose a proper distance threshold
    if(linkage[j][2]>200):break
for i in xrange(j):
    #print 1+max(clustdict)
    clust1= int(linkage[i][0])
    clust2= int(linkage[i][1])
    clustdict[max(clustdict)+1] = clustdict[clust1] + clustdict[clust2]
    del clustdict[clust1], clustdict[clust2]
print len(result),len(clustdict)
for i in clustdict:
    print len(clustdict[i]),
    lat = 0
    lon = 0
    for j in clustdict[i]:
        lat += result[j][0]
        lon += result[j][1]
    print round(lat/float(len(clustdict[i])),4),round(lon/float(len(clustdict[i])),4)
        
cur.close()
pm.close()
