# -*- coding: utf-8 -*-
"""
Created on Sun Apr 29 21:33:17 2018

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
import pandas as pd
nSpeaker = 75

nfiltbank = 20
def mfcc_generator(nfiltbank,dirt):
    coef_list = []
    nSpeaker = 5
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
(codebooks_mfcc) = training(nfiltbank,'/muss2')
for i in (codebooks_mfcc):
    for j in i:
        A = np.asarray(i).reshape(-1) 
    list1.append(A)
    
k = np.array(list1)
#X = np.append(values = k,arr = np.zeros((4,1280)).astype(int),axis = 0)    

(codebooks_test) = mfcc_generator(nfiltbank,'/testddf')
for i1 in (codebooks_test):
    for j1 in i1:
        A1 = np.asarray(i1).reshape(-1) 
    list11.append(A1)
    
k1 = np.array(list11)    





        
 

X_train = np.array(k)

X_test = np.array(k1)
# Feature Scaling

outer = []
for i in range(0,5):
    for j in range(0,15):
        q = i
        outer.append(q)
y = np.array(outer)

#y = np.array(list(range(nSpeaker)))

from sklearn import metrics
#CREATE YOUR CLASSIFIER HERE
from sklearn.ensemble import RandomForestClassifier
classifier = RandomForestClassifier(n_estimators = 10000, criterion = 'entropy', random_state = 0)

classifier.fit(X_train,y)

y_pred = classifier.predict(X_test)

y_predprob = classifier.predict_proba(X_test)


#plt.plot(y_predprob)
trainspeaker = 75
testspeaker = 5


#testspeaker = 5
#y_pred = classifier.predict_classes(X_test)
#y_predprob = classifier.predict(X_test)
sumok = 0
closedsetcorrectness = 0
sumnotok = 0
y_percent = (y_predprob)
y_ted = np.array(list(range(testspeaker)))   

listnew = []
y_new = list(y_percent)
nn = 0   
fuc = np.append(arr = y_ted,values= y_pred)  
fuclist = list(fuc)
for ff in fuclist:
   
    nn = ff
    for jj in y_new[nn]:
        if jj == max(y_new[nn]):
            
            maxi = jj
            listnew.append(maxi)
            print('Likelihood is '+str(maxi))
            if maxi>0:
                print(str(ff)+' of test set identifies itself with '+str(fuclist[ff+testspeaker])+' of train set')
                sumok += 1
                if str(ff) == str(fuclist[ff+testspeaker]):
                    closedsetcorrectness+=1
                    
                #print('probably correct')
            else:
                print('No match')
                sumnotok +=1
                
                
    if ff == (testspeaker-1):
        
        print('done')
        break
print(str(closedsetcorrectness)+' correctly predicted and '+str(sumok-closedsetcorrectness)+' wrongly predicted as FA') 
dd = np.array(listnew)
med = np.median(dd)

yyy = [0,1,2,3,4]

plt.plot(yyy,y_pred)
plt.scatter(y_pred,dd,color = 'red')
plt.xlabel('Actual class')
plt.ylabel('predicted class')
plt.show()