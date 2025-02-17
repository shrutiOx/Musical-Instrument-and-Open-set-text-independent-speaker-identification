# -*- coding: utf-8 -*-
"""
Created on Mon Apr 23 17:28:32 2018

@author: Shrutisarika
"""
from matplotlib import offsetbox
import matplotlib as mpl
import numpy as np
import matplotlib.pyplot as plt
import pandas as pd
from opensetwithadaptivethres import arraa
# Importing the dataset
dataset = pd.read_csv('uyghurfemale.csv')
X = dataset.iloc[:, :-1].values
y = dataset.iloc[:, 4].values
# Encoding categorical data
"""
# Encoding the Independent Variable
from sklearn.preprocessing import LabelEncoder, OneHotEncoder
labelencoder_X = LabelEncoder()
X[:, 3] = labelencoder_X.fit_transform(X[:, 3])
onehotencoder = OneHotEncoder(categorical_features = [3])
X = onehotencoder.fit_transform(X).toarray()
#Avoiding the dummy variable trap
X = X[:,1:]
"""

# Splitting the dataset into the Training set and Test set
from sklearn.cross_validation import train_test_split
X_train,X_test,y_train,y_test = train_test_split(X,y,test_size = 0.2,random_state = 0)

#will be creating random forest regressor later here
from sklearn.ensemble import RandomForestRegressor
regressor = RandomForestRegressor(n_estimators = 10000,random_state = 0)
regressor.fit(X_train,y_train)
y_pred = regressor.predict(X_test)

#visualizing polynomial regression and applying some effects ie lowering the inc steps for better prediction

#visualizing polynomial regression results

a = (arraa)[np.newaxis]
print (a)

y_predtest = regressor.predict(a)

#plotting the predicted values train set
#from mpl_toolkits.mplot3d import Axes3D
xs1 = X_train[:,0]
xs2 = X_train[:,2]
xs3 = X_train[:,3]
plt.scatter(xs1,y_train,color = 'red')
plt.scatter(xs2,y_train,color = 'green')
plt.scatter(xs3,y_train,color = 'yellow')



#plotting the ind and dep variable,scattered
plt.plot(X_train,regressor.predict(X_train),color = 'blue')#plotting x_train vs predicted y_train values by regressor
#plt.plot(X_test,regressor.predict(X_test),color = 'green')
plt.title('Salary vs Experience(Training Set)')
plt.xlabel('Experience')
plt.ylabel('Salary')
plt.show()

"""
#plotting the predicted values test set

plt.scatter(X_test,y_test,color = 'orange')#plotting test values(dep and ind)
plt.plot(X_train,regressor.predict(X_train),color = 'green')#the regreesion model is the same like that of train set
plt.title('Salary vs Experience(Test Set)')
plt.xlabel('Experience')
plt.ylabel('Salary')
plt.show()

"""
