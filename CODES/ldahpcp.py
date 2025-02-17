# -*- coding: utf-8 -*-
"""
Created on Tue Apr 24 21:11:33 2018

@author: Shrutisarika
"""

import numpy as np
from scipy.io.wavfile import read
from LBG import lbg,EUDistance
import essentia.standard as ess


import matplotlib.pyplot as plt
import os
from testing import ggp
def traind(nfil):
   
    fs = 44100
    nSpeaker = 30
    nCentroid = 32
    codebooks = np.empty((nSpeaker,nfil,nCentroid))
    #codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + '/testsamples';
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        x = ess.MonoLoader(filename = directory+fname, sampleRate = fs)()
        gg = np.transpose(ggp(x))
        codebooks[i,:,:] = lbg(gg, nCentroid)
    return (codebooks)
#nSpeaker = 10
nfil = 12
codebooks = traind(nfil)

A = 0
list1 = []
list11 = []
list2 = []

for i in (codebooks):
    for j in i:
        A = np.asarray(i).reshape(-1) 
    list1.append(A)
    
k = np.array(list1)
#X = np.append(values = k,arr = np.zeros((4,1280)).astype(int),axis = 0)    






        
 
outer = []
X_train = np.array(k)
#y = np.array(list(range(nSpeaker)))
#y = np.append(arr = y,values=np.zeros(25))

np.save('X_testhpcp-30',X_train)


       
       
       
    
    
    

   
       