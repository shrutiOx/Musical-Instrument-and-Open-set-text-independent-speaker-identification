# -*- coding: utf-8 -*-
"""
Created on Tue Apr 24 19:57:23 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
from scipy.io.wavfile import read

#from mel_coefficients import mfcc
import matplotlib.pyplot as plt

import os

import essentia.standard as ess



#from mel_coefficients import mfcc



from matplotlib import offsetbox
import sklearn
print(__doc__)


import matplotlib as mpl
from saliencetest import ggpsalience
nSpeaker = 15

nfiltbank = 50
def traind(nfil,dirt):
    #dir = '/train'
    fs = 44100
    nSpeaker = 15
    nfil = 50
    codebooks = np.empty((nSpeaker,nfil))
    #codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + dirt;
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        x = ess.MonoLoader(filename = directory+fname, sampleRate = fs)()
        gg = (ggpsalience(x))
        codebooks[i,:] = np.resize(gg, nfil)
    return (codebooks)
        
A = 0
list1 = []
list11 = []
list2 = []
(codebooks) = traind(nfiltbank,'/testmuss2')
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

np.save('X_testsal-15',X_train)