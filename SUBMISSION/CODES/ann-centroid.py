# -*- coding: utf-8 -*-
"""
Created on Sat Apr 28 17:17:54 2018

@author: Shrutisarika
"""

from __future__ import division
import numpy as np
import matplotlib.pyplot as plt
import os
from matplotlib import offsetbox

import matplotlib as mpl

from scipy.io.wavfile import read
from sklearn import (manifold, datasets, decomposition, ensemble,
                     discriminant_analysis, random_projection)
X_train = np.load('X_traincentroid10.npy')
X_test = np.load('X_testcentroid-30.npy')
outer = []
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
classifier.add(Dense(output_dim = 59, init = 'uniform', activation = 'relu', input_dim = 50))

# Adding the second hidden layer
classifier.add(Dense(output_dim = 59, init = 'uniform', activation = 'relu'))
classifier.add(Dense(output_dim = 59, init = 'uniform', activation = 'relu'))
classifier.add(Dense(output_dim = 59, init = 'uniform', activation = 'relu'))
#classifier.add(Dense(output_dim = 588, init = 'uniform', activation = 'relu'))
# Adding the output layer
classifier.add(Dense(output_dim = 75, init = 'uniform', activation = 'softmax'))

# Compiling the ANN
classifier.compile(optimizer = 'adam', loss = 'categorical_crossentropy', metrics = ['accuracy'])
onehot = keras.utils.to_categorical(y, num_classes=75)
#model.fit(X_train, onehot, epochs=100, batch_size=1000)
# Fitting the ANN to the Training set
classifier.fit(X_train, onehot, batch_size = 40 , nb_epoch = 1000)
# Generate dummy data

#548,50 for 5
# Train the model, iterating on the data in batches of 32 samples
#model.fit(data, one_hot_labels, epochs=10, batch_size=32)
#model.fit(X_train,y, batch_size = 32, nb_epoch = 10)

# Part 3 - Making the predictions and evaluating the model

# Predicting the Test set results
#y_pred = classifier.predict(X_test)
#y_pred = (y_pred > 0.5)


testspeaker = 5
y_pred = classifier.predict_classes(X_test)
y_predprob = classifier.predict(X_test)