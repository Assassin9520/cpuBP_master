# cpuBP_master
[EN] [application is in romanian] <br>
Web-based app for detecting the rate of correct Branch Predictions inside a custom software CPU, all packed in a beautiful UI.

---



# Description - What is cpuBP
<br>
<b>The cpuBP application</b> is a didactic software program, and more precisely, an INTERPRETER that implements a processor (simulated software), and implicitly, by its nature, implements an assembly language that works in the structure of the built processor, to make jump predictions for testing and the study of the efficiency of the latter in improving the performance of processor architectures.
<br>
The application was built for teaching purposes, to serve future students in the laboratory rooms to study how to make predictions of jump instruction in the processor, but also to study their effectiveness, all using only this application.
<br>
This allows, before the actual jump predictions, which present the purpose of the present paper, the writing of code in the custom assembly language integrated in it's functionality, in a simpler and more intelligible manner.
 .  
The build custom simulated CPU inside the application features the following BRANCH PREDICTORS:
-

---



# Prequisites
<br>PHP 5.5+
<br>XAMPP - Apache(for local webserver) or hosted web-server is fine.
<br>App will be started from index.php file (eg. http://localhost/cpuBP/index.php)


----



# Instructions on use 
<br>
Please refer to Pages/help.html , which contains all the details of the language and the instructions .
 
cpuBP ASM instructions(help about them in Pages/help.html)(These are example of use, not proof of concept):
 
JNZ 0xE300 
JNZ to_label_jnz #it is easier to create labels inside JUMP instructions
JPZ 0x0001 
JPZ to_label_jpz
eticheta_label_1:
JMP 0x1111
JMP _to_label #label in jump
JNC 0x1000
JNC to_label_jnc
JPC 0x1001
JPC to_label_jpc
JNN 0x1002
JNN to_label_jnn
JPN 0x1003
JPN to_label_jpn
JNO 0x1004
JNO to_label_jno
JPO 0x1005
JPO to_label_jpo
_to_label:
STR R3, R4, R5
STR R6 , 0xff 
LDR R7, R8, R9
LDR R10, 0x55
ADD R11, R12
ADD R13, 0xA
ADC R14, R0
SUB R0, R1
SUB R0, 0x6
SBC R1, R2
AND R0, R1
ORR R0,R1
XOR R0,R1
CMP R10,R11
INV R13
SHL R13
SHR R13
ROL R13
ROR R13
PSH R7
POP R7
CLZ
CLC
CLN
NOP   
HALT 

----



# Results 
<br> Interface explained:
<!-- ... -->
<!-- COMENTARIU: imagine cu interfata explicata cpuBP comes here... -->
![img1](https://i.imgur.com/zj70JEd.png)

<br>Tests:

-Running the application using quicksort algorithm 
bla bla, alte teste
<br>
<br>
<br>
<br>
<br>

