'''
Created on 2013-6-20
find ./ -type f ! -path '*/.svn/*' -print0 | xargs -0 md5sum > ./my.md5  
@author: Administrator
'''
import re

def convert(f_name):
    f_in = open(f_name,'r')
    f_out = open("result",'a+')
    
    for each in f_in:
        each=each.strip('\n')
        l = re.split('  ./', each)
        print l[1]
#       re='.c|.h|.phpt|.sln|.w32|.m4|.vcproj|.xml|.a|.lib'
        res = re.match('.', l[1])
        if res is None:
            role='doc'
        else:
            role='src'
            
        s='<file md5sum="'+l[0]+'" name="'+l[1]+'" role='+'"'+role+'" />'+'\n'
        f_out.write(s)
    f_in.close()
    f_out.close()

if __name__ == '__main__':
    convert('my.md5')
