# -*- coding: utf-8 -*-
"""
Created on Sat Mar 24 23:36:22 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import EUDistance
#from mel_coefficients import mfcc
from sklearn import preprocessing
from train2 import training
import os
from python_speech_features import mfcc
import math
nSpeaker = 100
nfiltbank = 20
#orderLPC = 15
(codebooks_mfcc) = training(nfiltbank,'/train')
directory = os.getcwd() + '/test';
fname = str()
nCorrect_MFCC = 0
nCorrect_LPC = 0
valuei = []
minimaa = 0
list3 = []

def minDistance(features, codebooks):
    speaker = 0
    
    distmin = np.inf
    for k in range(np.shape(codebooks)[0]):
        
        D = EUDistance(features, codebooks[k,:,:])
        dist = np.sum(np.min(D, axis = 1))/(np.shape(D)[0]) 
        if dist < distmin:
            distmin = dist
            speaker = k
        #print(dist)
        valuei.append(dist)
    #print(valuei)
 
       
    return speaker




for i in range(nSpeaker):
    fname = '/s' + str(i+1) + '.wav'
    print('Now speaker ', str(i+1), 'features are being tested')
    (fs,s) = read(directory + fname)
    #mel_coefs = mfcc(s,fs,nfiltbank)
    
    mel_coefs = np.transpose(mfcc(s,fs))
   # lpc_coefs = lpc(s, fs, orderLPC)
    sp_mfcc = minDistance(mel_coefs, codebooks_mfcc)
    
    sum = 0
    for inc in valuei:
        sum += inc**2
    #print(sum)
    fe = math.sqrt(sum)
    #print(f)
    listi = []
    for step in valuei:
        normi = step/fe
        listi.append(normi)
    #print(listi)
    minimaa = np.min(listi)
    print("minimum is")
    list3.append(minimaa)
    print(minimaa*100)
    valuei = []
    listi = []
    
    #sp_lpc = minDistance(lpc_coefs, codebooks_lpc)
    if (minimaa*100)>20.2:
        print('No match')
    else:
        
        print('Speaker ', (i+1), ' in test matches with speaker ', (sp_mfcc+1), ' in train for training with MFCC')
   # print('Speaker ', (i+1), ' in test matches with speaker ', (sp_lpc+1), ' in train for training with LPC')
   
    if i == sp_mfcc:
        nCorrect_MFCC += 1
    
    

percentageCorrect_MFCC = (nCorrect_MFCC/nSpeaker)*100
print('Accuracy of result for training with MFCC is ', percentageCorrect_MFCC, '%')

#z = preprocessing.normalize(val)
#z = norm(val)
sumaa = 0
for h in list3:
    sumaa += h
    
    
