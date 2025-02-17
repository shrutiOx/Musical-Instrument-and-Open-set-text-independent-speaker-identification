# -*- coding: utf-8 -*-
"""
Created on Mon Mar 26 22:25:37 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import lbg

from LPC import lpc
import matplotlib.pyplot as plt
import os


def training(orderLPC,dir):
    nSpeaker = 150
    nCentroid = 64
   
    codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + dir;
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        (fs,s) = read(directory + fname)
       
        lpc_coeff = lpc(s, fs, orderLPC)
        
        codebooks_lpc[i,:,:] = lbg(lpc_coeff, nCentroid)
        
        
    return ( codebooks_lpc)
    
    
