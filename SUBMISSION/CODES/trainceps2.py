# -*- coding: utf-8 -*-
"""
Created on Sat Apr 28 21:27:27 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import lbg
#from mel_coefficients import mfcc

import matplotlib.pyplot as plt
import os
from cepsanal2 import ceps

def training(nfiltbank,dir):
    nSpeaker = 150
    nCentroid = 64
    codebooks_ceps = np.empty((nSpeaker,nfiltbank,nCentroid))
    #codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + dir;
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        (fs,s) = read(directory + fname)
        #mel_coeff = mfcc(s, fs, nfiltbank)
        #print(mel_coeff)
        
        mel_coefs = np.transpose(ceps(s,fs))
        #lpc_coeff = lpc(s, fs, orderLPC)
        codebooks_ceps[i,:,:] = lbg(mel_coefs, nCentroid)
       # codebooks_lpc[i,:,:] = lbg(lpc_coeff, nCentroid)
        
        
    #plotting 5th and 6th dimension MFCC features on a 2D plane
    #comment lines 54 to 71 if you don't want to see codebook
   
    
   
    
    return (codebooks_ceps)
    
    
