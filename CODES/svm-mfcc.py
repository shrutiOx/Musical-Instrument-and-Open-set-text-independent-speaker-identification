# -*- coding: utf-8 -*-
"""
Created on Sun Apr 29 21:36:26 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import EUDistance,lbg
#from mel_coefficients import mfcc
import matplotlib.pyplot as plt
from train2 import training
import os
from python_speech_features import mfcc

nSpeaker = 150

nfiltbank = 20
def mfcc_generator(nfiltbank,dirt):
    coef_list = []
    nSpeaker = 30
    nCentroid = 64
    mfcc_gen = np.empty((nSpeaker,nfiltbank,nCentroid))
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
        mfcc_gen[i,:,:] = lbg(mel_coefs, nCentroid)
    return (mfcc_gen)
        
A = 0
list1 = []
list11 = []
list2 = []
(codebooks_mfcc) = training(nfiltbank,'/muss3')
for i in (codebooks_mfcc):
    for j in i:
        A = np.asarray(i).reshape(-1) 
    list1.append(A)
    
k = np.array(list1)
#X = np.append(values = k,arr = np.zeros((4,1280)).astype(int),axis = 0)    

(codebooks_test) = mfcc_generator(nfiltbank,'/testsamples')
for i1 in (codebooks_test):
    for j1 in i1:
        A1 = np.asarray(i1).reshape(-1) 
    list11.append(A1)
    
k1 = np.array(list11)    





        
 

X_train = np.array(k)
outer = []

for i in range(0,10):
    for j in range(0,15):
        q = i
        outer.append(q)
y = np.array(outer)

#y = np.array(list(range(nSpeaker)))
from sklearn.svm import SVC
classifier = SVC(kernel = 'linear',random_state = 0)
classifier.fit(X_train,y)

X_test = np.array(k1)


#predicting test set results
y_pred = classifier.predict(X_test)
#from sklearn.metrics import confusion_matrix
#cm = confusion_matrix(y,y_pred)


#from sklearn import metrics
#accurary = metrics.accuracy_score(y,y_pred)

outer1w = []
for iw in range(0,10):
    for jw in range(0,3):
        qw = iw
        outer1w.append(qw)
y1 = np.array(outer1w)

#from sklearn.metrics import confusion_matrix
#cm = confusion_matrix(y1,y_pred)

#plt.plot(y1,y_pred)

plt.scatter(y1,y_pred)
plt.xlabel('actual class')
plt.ylabel('predicted class')
plt.show()
plt.plot(y1,y_pred)
plt.xlabel('actual class')
plt.ylabel('predicted class')
plt.show()