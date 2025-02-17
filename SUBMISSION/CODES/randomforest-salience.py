# -*- coding: utf-8 -*-
"""
Created on Sat Apr 28 15:18:32 2018

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
X_train = np.load('X_trainsalience10.npy')
X_test = np.load('X_testsal-30.npy')
outer = []
for i in range(0,10):
    for j in range(0,15):
        q = i
        outer.append(q)
y = np.array(outer)

from sklearn import metrics
from sklearn.ensemble import RandomForestClassifier
classifier = RandomForestClassifier(n_estimators = 10000, criterion = 'entropy', random_state = 0)

classifier.fit(X_train,y)

y_pred = classifier.predict(X_test)

y_predprob = classifier.predict_proba(X_test)