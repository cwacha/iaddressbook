#!/usr/bin/env python

import xmlrpclib
import pprint

url = 'http://domain.com/iaddressbook/xmlrpc.php'
api_key='abcd'

proxy = xmlrpclib.ServerProxy(url)

try:
	print "Available Methods"
	print proxy.system.listMethods()
	print
	print "version (with non-existent api_key)"
	print proxy.version('key_does_not_exist')
	print
	print "version"
	print proxy.version(api_key)
	print
	print "count_contacts"
	print proxy.count_contacts(api_key, '')
	print
	print "get_contacts query='' limit=1 offset=0"
	print proxy.get_contacts(api_key, '', 1, 0)
	print
	print "get_contacts query='test' limit=1000 offset=0"
	print proxy.get_contacts(api_key, 'test', 1000, 0)

except xmlrpclib.ProtocolError as e:
	print "ERROR: A protocol error occurred"
	print "URL: %s" % e.url
	print "HTTP/HTTPS headers: %s" % e.headers
	print "Error code: %d" % e.errcode
	print "Error message: %s" % e.errmsg
except xmlrpclib.Fault as e:
	print "ERROR: %s [code=%s url=%s]" % (e.faultString, e.faultCode, url)
except Exception as e:
	print "ERROR: Unexpected error:", e
