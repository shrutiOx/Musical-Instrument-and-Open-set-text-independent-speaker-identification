
tic;
mod = trainugr('F:\DATABASES\TRAIN3_REV\',50);
%res = distminigmm('F:\DATABASES\TESTMALE\',40,mod);
%test1gmm('F:\DATABASES\TESTMALE\',100,mod);
  %testugy('F:\DATABASES\TESTMALE\',50,mod,res);
%[hoodgmm] = likeligmm('F:\DATABASES\TRAIN3_REV\',5,mod);
%testloggmm('F:\DATABASES\TESTMALE\',60,mod,hoodgmm);
  % [sdgmm] = thresgmm('F:\DATABASES\TESTFEM\',40,mod);
 % testnorm('F:\DATABASES\TESTFEM\',40,mod);
  
 %  voda = trainubm('F:\DATABASES\TRAIN3_REV\', 5);
   
   ubmgmm = likelihoodgmm('F:\DATABASES\TESTMALE\', 100, mod);
  testubmgmm('F:\DATABASES\TEST3\', 50, mod,ubmgmm);
   toc;