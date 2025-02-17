# -*- coding: utf-8 -*-
"""
Created on Fri Apr  6 01:48:48 2018

@author: Shrutisarika
"""

import numpy as np
from scipy.io.wavfile import read


#from my_hpcp import ggp

import essentia.standard as ess
import os

def ggpsalience(x):
    fs = 44100
    H = 1024
    M = 2048
    N = 2*M
    guessUnvoiced = True

    window = ess.Windowing(type='hann', zeroPadding=N-M) 
    spectrum = ess.Spectrum(size=N)
    spectralPeaks = ess.SpectralPeaks(minFrequency=50, maxFrequency=10000, maxPeaks=100, sampleRate=fs, 
				magnitudeThreshold=0, orderBy="magnitude")
    pitchSalienceFunction = ess.PitchSalienceFunction()
    pitchSalienceFunctionPeaks = ess.PitchSalienceFunctionPeaks(minFrequency=100, maxFrequency=300)
    
    
    x = ess.EqualLoudness()(x)
    totalSaliences = []
    for frame in ess.FrameGenerator(x, frameSize=M, hopSize=H):
        frame = window(frame)
        mX = spectrum(frame)
        peak_frequencies, peak_magnitudes = spectralPeaks(mX)
        pitchSalienceFunction_vals = pitchSalienceFunction(peak_frequencies, peak_magnitudes)
        salience_peaks_bins_vals, salience_peaks_saliences_vals = pitchSalienceFunctionPeaks(pitchSalienceFunction_vals)
        totalSaliences.append(max(salience_peaks_saliences_vals))

    totalSaliences = np.array(totalSaliences)
    return totalSaliences

fs = 44100
x = ess.MonoLoader(filename = 'piano.wav', sampleRate = fs)()
f = ggpsalience(x)