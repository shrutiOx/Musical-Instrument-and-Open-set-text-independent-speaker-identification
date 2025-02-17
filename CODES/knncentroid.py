# -*- coding: utf-8 -*-
"""
Created on Sat Apr 28 14:04:11 2018

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
from sklearn.neighbors import KNeighborsClassifier
from sklearn import metrics
classifier = KNeighborsClassifier(n_neighbors = 1,metric='minkowski',p = 2)
classifier.fit(X_train,y)
y_pred = classifier.predict(X_test)