# -*- coding: utf-8 -*-
"""
Created on Tue Apr 24 20:55:49 2018

@author: Shrutisarika
"""

from matplotlib import offsetbox
import sklearn
print(__doc__)
import numpy as np
import essentia.standard as ess
import os
import matplotlib as mpl
from testingcentroid import ggpcen
nSpeaker = 30

nfiltbank = 50
def traind(nfil,dirt):
 
    fs = 44100
    nSpeaker = 30
    nfil = 50
    codebooks = np.empty((nSpeaker,nfil))
    #codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + dirt;
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        x = ess.MonoLoader(filename = directory+fname, sampleRate = fs)()
        gg = (ggpcen(x))
        codebooks[i,:] = np.resize(gg, nfil)
    return (codebooks)
        
A = 0
list1 = []
list11 = []
list2 = []
(codebooks) = traind(nfiltbank,'/testsamples')
for i in (codebooks):
    for j in i:
        A = np.asarray(i).reshape(-1) 
    list1.append(A)
    
k = np.array(list1)


        
 
outer = []
X_train = np.array(k)

#y = np.array(list(range(nSpeaker)))
#y = np.append(arr = y,values=np.zeros(25))

np.save('X_testcentroid-30',X_train)
