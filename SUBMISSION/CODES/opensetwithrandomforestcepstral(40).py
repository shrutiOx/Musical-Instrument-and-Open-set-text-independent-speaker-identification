# -*- coding: utf-8 -*-
"""
Created on Mon Apr 30 03:38:42 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import EUDistance,lbg
#from mel_coefficients import mfcc
import matplotlib.pyplot as plt
from trainceps2 import training
import os
from python_speech_features import mfcc
import pandas as pd
from cepsanal2 import ceps
nSpeaker = 15

nfiltbank = 40
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
       
        mel_coefs = np.transpose(ceps(s,fs))
        coef_list.append(mel_coefs)
        #lpc_coeff = lpc(s, fs, orderLPC)
        mfcc_gen[i,:,:] = lbg(mel_coefs, nCentroid)
    return (mfcc_gen)
def mfcc_generatortrain(nfiltbank,dirt):
    coef_list = []
    nSpeaker =15
    nCentroid = 64
    mfcc_gentrain = np.empty((nSpeaker,nfiltbank,nCentroid))
    #codebooks_lpc = np.empty((nSpeaker, orderLPC, nCentroid))
    directory = os.getcwd() + dirt;
    fname = str()

    for i in range(nSpeaker):
        fname = '/s' + str(i+1) + '.wav'
        print('Now speaker ', str(i+1), 'features are being trained' )
        (fs,s) = read(directory + fname)
        #mel_coeff = mfcc(s, fs, nfiltbank)
        #print(mel_coeff)
       
        mel_coefs = np.transpose(ceps(s,fs))
        coef_list.append(mel_coefs)
        #lpc_coeff = lpc(s, fs, orderLPC)
        mfcc_gentrain[i,:,:] = lbg(mel_coefs, nCentroid)
    return (mfcc_gentrain)
        
A = 0
list1 = []
list11 = []
list2 = []
(codebooks_mfcc) = training(nfiltbank,'/trainlibri')
for i in (codebooks_mfcc):
    for j in i:
        A = np.asarray(i).reshape(-1) 
    list1.append(A)
    
k = np.array(list1)
#X = np.append(values = k,arr = np.zeros((4,1280)).astype(int),axis = 0)    

(codebooks_test) = mfcc_generator(nfiltbank,'/testlibri')
for i1 in (codebooks_test):
    for j1 in i1:
        A1 = np.asarray(i1).reshape(-1) 
    list11.append(A1)
    
k1 = np.array(list11)    
listd = []
(codebooks_train) = mfcc_generatortrain(nfiltbank,'/testlibri')
for i11 in (codebooks_train):
    for j11 in i11:
        A11 = np.asarray(i11).reshape(-1) 
    listd.append(A11)
    
ktrain = np.array(listd)    


trainspeaker = 15
testspeaker = 30


X_train = np.array(k)

y = np.array(list(range(nSpeaker)))
y_ted = np.array(list(range(testspeaker)))
#y = np.append(arr = y,values=np.zeros(25))
X_test = np.array(k1)
ktrainarr = np.array(ktrain)
# Feature Scaling

from sklearn import metrics
#CREATE YOUR CLASSIFIER HERE
from sklearn.ensemble import RandomForestClassifier
classifier = RandomForestClassifier(n_estimators = 10000, criterion = 'entropy', random_state = 0)

classifier.fit(X_train,y)

y_pred = classifier.predict(X_test)
#accurary = metrics.accuracy_score(y,y_pred)

y_predprob = classifier.predict_proba(X_test)
#####################################################################
classifiertrain = RandomForestClassifier(n_estimators = 10000, criterion = 'entropy', random_state = 0)

classifiertrain.fit(X_train,y)

y_predtrain = classifier.predict(ktrainarr)
#accurary = metrics.accuracy_score(y,y_pred)

y_predprobtrain = classifier.predict_proba(ktrainarr)
##############################################################################

from sklearn.metrics import confusion_matrix
#cm = confusion_matrix(y,y_pred)
sumok = 0
closedsetcorrectness = 0
sumnotok = 0
y_percent = (y_predprob*100)
y_ptrain = (y_predprobtrain*100)
"""
for ii in y_percent:
    for jj in ii:
        if jj == max(ii):
            maxi = jj
            if maxi>15:
                sumok+=1
                print('may match')
                
            else:
                sumnotok+=1
                print('no match')
            
                
y_new = []
"""
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
            if maxi>20:
                print('Speaker '+str(ff)+' of test set identifies itself with '+str(fuclist[ff+testspeaker])+' of train set')
                sumok += 1
                if str(ff) == str(fuclist[ff+testspeaker]):
                    closedsetcorrectness+=1
                    
                #print('probably correct')
            else:
                print('Speaker '+str(ff)+'is unknown')
                sumnotok +=1
                
                
    if ff == (testspeaker-1):
        
        print('done')
        break
print(str(closedsetcorrectness)+' correctly predicted and '+str(sumok-closedsetcorrectness)+' wrongly predicted as FA')


dd = np.array(listnew)
meanwithtest = np.mean(dd)

listnewtrain = []
y_newtrain = list(y_ptrain)
nn1 = 0   
fuc1 = np.array(y_predtrain)  
fuclist1 = list(fuc1)
for ff1 in fuclist1:
   
    nn1 = ff1
    for jj1 in y_newtrain[nn1]:
        if jj1 == max(y_newtrain[nn1]):
            
            maxim = jj1
            listnewtrain.append(maxim)
dd1 = np.array(listnewtrain)
meanwithtrain = np.mean(dd1)

a = trainspeaker
b = testspeaker
c = meanwithtrain
d = meanwithtest
listee = [a,b,c,d]
arraa = np.array(listee)
