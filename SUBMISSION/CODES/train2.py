
from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import lbg
#from mel_coefficients import mfcc

import matplotlib.pyplot as plt
import os
from python_speech_features import mfcc

def training(nfiltbank,dirt):
    coef_list = []
    nSpeaker = 150#50
    nCentroid = 64
    codebooks_mfcc = np.empty((nSpeaker,nfiltbank,nCentroid))
    #codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + dirt;
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        (fs,s) = read(directory + fname)
        #mel_coeff = mfcc(s, fs, nfiltbank)
        #print(mel_coeff)
        
        mel_coefs = np.transpose(mfcc(s,fs))
        coef_list.append(mel_coefs)
        #lpc_coeff = lpc(s, fs, orderLPC)
        codebooks_mfcc[i,:,:] = lbg(mel_coefs, nCentroid)
        
    #plt.figure(nSpeaker + 1)
    #print(coef_list)
    
    #c1 = plt.scatter(coef_list[0,6,:],coef_list[0,4,:],s = 100, color = 'red', marker = '+')
    #c2 = plt.scatter(codebooks_mfcc[1,6,:], codebooks_mfcc[1,4,:], s = 500, color = 'blue',cmap='viridis', marker = 'o')
    #c3 = plt.scatter(codebooks_mfcc[2,6,:], codebooks_mfcc[2,4,:], s = 100, color = 'grey', marker = '+')
    #c4 = plt.scatter(codebooks_mfcc[3,6,:], codebooks_mfcc[3,4,:], s = 100, color = 'yellow', marker = '+')
    #c5 = plt.scatter(codebooks_mfcc[4,6,:], codebooks_mfcc[4,4,:],s = 100, color = 'violet', marker = '+')
    #c6 = plt.scatter(codebooks_mfcc[5,6,:], codebooks_mfcc[5,4,:], s = 100, color = 'pink', marker = '+')
    #c7 = plt.scatter(codebooks_mfcc[6,6,:], codebooks_mfcc[6,4,:], s = 100, color = 'orange', marker = '+')
    #c8 = plt.scatter(codebooks_mfcc[7,6,:], codebooks_mfcc[7,4,:], s = 100, color = 'grey', marker = '+')
    #plt.grid()
    #plt.legend((c1, c2,c3,c4), ('Sp1 centroids', 'Sp2 centroids','Sp3 centroids', 'Sp4 centroids'), scatterpoints = 1, loc = 'upper left')    
    #plt.show()
   

    
   
        
       
        
        
    #plotting 5th and 6th dimension MFCC features on a 2D plane
    #comment lines 54 to 71 if you don't want to see codebook
   
    
   
    
    return (codebooks_mfcc)
    
    
