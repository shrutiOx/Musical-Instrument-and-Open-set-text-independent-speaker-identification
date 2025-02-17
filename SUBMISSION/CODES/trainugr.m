function mod = trainugr(traindir, u, y)
% Speaker Recognition: Training Stage
%
% Input:
%       traindir : string name of directory contains all train sound files
%       n        : number of train files in traindir
%
% Output:
%       code     : trained VQ codebooks, code{i} for i-th speaker
%
% Note:
%       Sound files in traindir is supposed to be: 
%                       s1.wav, s2.wav, ..., sn.wav
% Example:
%       >> code = train('C:\data\train\', 8,6);


for i = 1:u  
    % train a VQ codebook for each speaker
    file = sprintf('%ss%d.wav', traindir, i);           
    disp(file);
   
 [s fs] = wavread(file);
 lwav = length(s)-1;





    
    v = mfcc1(s, fs);  % Compute MFCC's
    cv = vqlbg1(v,40);% Train VQ codebook
  k = 18;
    [z1,model,llh] = mixGaussEm(cv,2000);
    

    
    
   mod{i} = model.mu;
    
 


    
    
end