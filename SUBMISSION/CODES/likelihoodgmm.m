function ubmgmm = likelihoodgmm(testdir, n, mod)
% Speaker Recognition: Testing Stage
%
% Input:
% testdir : string name of directory contains all test sound files
% n : number of test files in testdir
% code : codebooks of all trained speakers
%
% Note:
% Sound files in testdir is supposed to be:
% s1.wav, s2.wav, ..., sn.wav
%
% Example:
% >> test('C:\data\test\', 8, code);
for k = 1:n
% read test sound file of each speaker
file = sprintf('%ss%d.wav', testdir, k);
[s, fs] = wavread(file);
lwav = length(s)-1;
%fft of wave and its features
%wavefft=abs(fft(s1));
%o = length(wavefft);
%figure(2);
%plot (wavefft);
%grid
%xlabel('Frequency in HZ');
%ylabel('Magnitude');
%title('The Wave FFT');
v = mfcc1(s, fs);
cv = vqlbg1(v,32);% Compute MFCC's
distmin = inf;
k1 = 0;
sumq = 0;

    [z1,model,llh] = mixGaussEm(cv,40);
     Mu1 = model.mu;
for l = 1:length(mod) % each trained codebook, compute distortion
d = disteu1(Mu1, mod{l});
dist = sum(min(d,[],2)) / size(d,1);
if dist<distmin
distmin = dist;
k1 = l;
end



end
%suma = 0;

%disp(z);

disp(distmin);

%suma = 0;

%disp(dist);
%sumq = sumq+dist;
ubmgmm{k} = distmin;
%distm{k} = distmin;




end

end

