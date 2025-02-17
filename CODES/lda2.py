# -*- coding: utf-8 -*-
"""
Created on Tue Apr 24 20:28:02 2018

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
X_train = np.load('X_trainsalience-pitch.npy')
outer = []
for i in range(0,5):
    for j in range(0,14):
        q = i
        outer.append(q)
y = np.array(outer)
"""
from sklearn.preprocessing import StandardScaler
sc = StandardScaler()
X_train = sc.fit_transform(X_train)
X_test = sc.transform(X_test)

"""



# Applying LDA

from sklearn import discriminant_analysis
lda = discriminant_analysis.LinearDiscriminantAnalysis(n_components = 2)
X_trainlda = lda.fit_transform(X_train, y)

############################################################################


#####################################################################################
N = 5
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
plt.title('Plot of LDA components of musical instruments when trained with Salience coeffs')
cb = plt.colorbar(scat, spacing='proportional',ticks=bounds)
plt.savefig('ldacentroidpitch')
plt.show()
#plt.savefig('ldahpcppitch')
