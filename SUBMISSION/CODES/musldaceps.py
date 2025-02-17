# -*- coding: utf-8 -*-
"""
Created on Sat Apr 21 21:35:09 2018

@author: Shrutisarika
"""

#clustering for music 
from __future__ import division
import numpy as np
from scipy.io.wavfile import read
from LBG import EUDistance,lbg
#from mel_coefficients import mfcc
import matplotlib.pyplot as plt
from trainceps2 import training
import os
from cepsanal2 import ceps
from python_speech_features import mfcc
from matplotlib import offsetbox
from sklearn import (manifold, datasets, decomposition, ensemble,
                     discriminant_analysis, random_projection)
print(__doc__)
from time import time

import matplotlib as mpl
nSpeaker = 75

nfiltbank = 40
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
       
        mel_coefs = np.transpose(ceps(s,fs))
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



# Scale and visualize the embedding vectors

 
outer = []
X_train = np.array(k)
for i in range(0,5):
    for j in range(0,15):
        q = i
        outer.append(q)
y = np.array(outer)
#y = np.append(arr = y,values=np.zeros(25))
X_test = np.array(k1)
#############data preprocess over##############
"""
# Applying PCA
from sklearn.decomposition import PCA
pca = PCA(n_components = 2)
X_trainpca = pca.fit_transform(X_train)
X_testpca = pca.transform(X_test)
explained_variance = pca.explained_variance_ratio_
"""
# Applying LDA
from sklearn.discriminant_analysis import LinearDiscriminantAnalysis as LDA
lda = LDA(n_components = 2)
X_trainlda = lda.fit_transform(X_train, y)
X_testlda = lda.transform(X_test)

N = 5
cmap = plt.cm.jet
# extract all colors from the .jet map
cmaplist = [cmap(i) for i in range(cmap.N)]
# create the new map
cmap = cmap.from_list('Custom cmap', cmaplist, cmap.N)

bounds = np.linspace(0,N,N+1)
norm = mpl.colors.BoundaryNorm(bounds, cmap.N)

print("Computing LDA projection")

scat = plt.scatter(X_trainlda[:, 0], X_trainlda[:, 1],c=y,s=np.random.randint(50,80,N),cmap=cmap,norm=norm)
# create the colorbar
cb = plt.colorbar(scat, spacing='proportional',ticks=bounds)
plt.xlabel('component1')
plt.ylabel('component2')
plt.title('lda plot with cepstral coeffs')

plt.show()
"""
scat = plt.scatter(X_train[:, 0], X_train[:, 1],c=y,s=np.random.randint(50,100,N),cmap=cmap,norm=norm)
# create the colorbar
cb = plt.colorbar(scat, spacing='proportional',ticks=bounds)
plt.show()
"""