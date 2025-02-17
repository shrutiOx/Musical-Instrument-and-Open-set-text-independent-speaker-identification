# -*- coding: utf-8 -*-
"""
Created on Fri Apr  6 00:43:00 2018

@author: Shrutisarika
"""

import numpy as np
from scipy.io.wavfile import read


#from my_hpcp import ggp

import essentia.standard as ess
import os

def ggpcen(x):
    M = 1024
    N = 1024
    H = 512
    fs = 44100
    spectrum = ess.Spectrum(size=N)
    window = ess.Windowing(size=M, type='hann')
    centroid = ess.Centroid(range=fs/2.0)
    
    centroids = []
    for frame in ess.FrameGenerator(x, frameSize=M, hopSize=H, startFromZero=True):          
         mX = spectrum(window(frame))        
         centroid_val = centroid(mX)
         centroids.append(centroid_val)            
    centroids = np.array(centroids)
    return (centroids)
fs = 44100


x = ess.MonoLoader(filename = 'piano.wav', sampleRate = fs)()
f = ggpcen(x)