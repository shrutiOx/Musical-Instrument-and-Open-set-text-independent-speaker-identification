# -*- coding: utf-8 -*-
"""
Created on Sat Apr 28 18:07:42 2018

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



#from mel_coefficients import mfcc



from matplotlib import offsetbox
from sklearn import (manifold, datasets, decomposition, ensemble,
                     discriminant_analysis, random_projection)
print(__doc__)


import matplotlib as mpl
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
        #mel_coefs = np.transpose(ceps(s,fs))
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

(codebooks_test) = mfcc_generator(nfiltbank,'/testre')
for i1 in (codebooks_test):
    for j1 in i1:
        A1 = np.asarray(i1).reshape(-1) 
    list11.append(A1)
    
k1 = np.array(list11)    





        
 
outer = []
X_train = np.array(k)
#y = np.array(list(range(nSpeaker)))
#y = np.append(arr = y,values=np.zeros(25))
X_test = np.array(k1)

for i in range(0,10):
    for j in range(0,15):
        q = i
        outer.append(q)
y = np.array(outer)
from sklearn import metrics
#CREATE YOUR CLASSIFIER HERE
# Importing the Keras libraries and packages
import keras
from keras.models import Sequential
from keras.layers import Dense

# Initialising the ANN
classifier = Sequential()
from keras.utils import to_categorical
#y_binary = to_categorical(y)

# Adding the input layer and the first hidden layer
classifier.add(Dense(output_dim = 700, init = 'uniform', activation = 'relu', input_dim = 1280))

# Adding the second hidden layer
classifier.add(Dense(output_dim = 700, init = 'uniform', activation = 'relu'))
classifier.add(Dense(output_dim = 700, init = 'uniform', activation = 'relu'))
classifier.add(Dense(output_dim = 700, init = 'uniform', activation = 'relu'))
#classifier.add(Dense(output_dim = 700, init = 'uniform', activation = 'relu'))
# Adding the output layer
classifier.add(Dense(output_dim = 150, init = 'uniform', activation = 'softmax'))

# Compiling the ANN
classifier.compile(optimizer = 'adam', loss = 'categorical_crossentropy', metrics = ['accuracy'])
onehot = keras.utils.to_categorical(y, num_classes=150)
#model.fit(X_train, onehot, epochs=100, batch_size=1000)
# Fitting the ANN to the Training set
classifier.fit(X_train, onehot, batch_size = 32, nb_epoch = 100)
# Generate dummy data


# Train the model, iterating on the data in batches of 32 samples
#model.fit(data, one_hot_labels, epochs=10, batch_size=32)
#model.fit(X_train,y, batch_size = 32, nb_epoch = 10)

# Part 3 - Making the predictions and evaluating the model

# Predicting the Test set results
#y_pred = classifier.predict(X_test)
#y_pred = (y_pred > 0.5)


testspeaker = 30
y_pred = classifier.predict_classes(X_test)
y_predprob = classifier.predict(X_test)



# Applying LDA
from sklearn.discriminant_analysis import LinearDiscriminantAnalysis as LDA
lda = LDA(n_components = 2)
X_trainlda = lda.fit_transform(X_train, y)
X_testlda = lda.transform(X_test)
############################################################################


#####################################################################################
N = 10
cmap = plt.cm.jet
# extract all colors from the .jet map
cmaplist = [cmap(i) for i in range(cmap.N)]
# create the new map
cmap = cmap.from_list('Custom cmap', cmaplist, cmap.N)

bounds = np.linspace(0,N,N+1)
norm = mpl.colors.BoundaryNorm(bounds, cmap.N)

print("Computing LDA projection")


scat = plt.scatter(X_trainlda[:, 0], X_trainlda[:, 1],c=y,s=np.random.randint(50,100,N),cmap=cmap,norm=norm)

# create the colorbar
plt.xlabel('component 1')
plt.ylabel('component 2')
plt.title('LDA components of when trained with mfcc coeffs')
cb = plt.colorbar(scat, spacing='proportional',ticks=bounds)
plt.show()

outer1w = []
for iw in range(0,10):
    for jw in range(0,3):
        qw = iw
        outer1w.append(qw)
y1 = np.array(outer1w)

#from sklearn.metrics import confusion_matrix
#cm = confusion_matrix(y1,y_pred)
plt.scatter(y1,y_pred)
plt.xlabel('actual class')
plt.ylabel('predicted class')
plt.show()
plt.plot(y1,y_pred)
plt.xlabel('actual class')
plt.ylabel('predicted class')
plt.show()

from sklearn import metrics
accurary = metrics.accuracy_score(y1,y_pred)

plt.plot(y_predprob)
plt.xlabel('class index')
plt.ylabel('Probability of Membership')

#plt.legend([red_dot, (red_dot, white_cross)], ["test", "Attr A+B"])
plt.legend(['test1','test2','test3','test4','test5','test6','test7','test8','test9','test10'],loc = '0')
plt.show()
