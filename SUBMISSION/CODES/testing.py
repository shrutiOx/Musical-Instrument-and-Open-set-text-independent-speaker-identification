# -*- coding: utf-8 -*-
"""
Created on Thu Apr  5 20:25:59 2018

@author: Shrutisarika
"""

import numpy as np
from scipy.io.wavfile import read


#from my_hpcp import ggp

import essentia.standard as ess
import os

def ggp(x):
    M = 1024
    N = 1024
    H = 512
    fs = 44100
    spectrum = ess.Spectrum(size=N)
    window = ess.Windowing(size=M, type='hann')
    spectralPeaks = ess.SpectralPeaks()
    hpcp = ess.HPCP()
#x = ess.MonoLoader(filename = 'piano.wav', sampleRate = fs)()
    hpcps = []

    for frame in ess.FrameGenerator(x, frameSize=M, hopSize=H, startFromZero=True):          
        mX = spectrum(window(frame))
        spectralPeaks_freqs, spectralPeaks_mags = spectralPeaks(mX) 
        hpcp_vals = hpcp(spectralPeaks_freqs, spectralPeaks_mags)
        hpcps.append(hpcp_vals)            
    hpcps = np.array(hpcps)
    return hpcps
fs = 44100


x = ess.MonoLoader(filename = 'piano.wav', sampleRate = fs)()
f = ggp(x)
#nfil = 12
#(hpcps) = training(nfil)
"""
M = 1024
N = 1024
H = 512
fs = 44100
spectrum = ess.Spectrum(size=N)
window = ess.Windowing(size=M, type='hann')
spectralPeaks = ess.SpectralPeaks()
hpcp = ess.HPCP()
#x = ess.MonoLoader(filename = 'piano.wav', sampleRate = fs)()
hpcps = []

for frame in ess.FrameGenerator(x, frameSize=M, hopSize=H, startFromZero=True):          
  mX = spectrum(window(frame))
  spectralPeaks_freqs, spectralPeaks_mags = spectralPeaks(mX) 
  hpcp_vals = hpcp(spectralPeaks_freqs, spectralPeaks_mags)
  hpcps.append(hpcp_vals)            
hpcps = np.array(hpcps)
"""