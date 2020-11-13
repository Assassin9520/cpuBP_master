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
<br/><br/>

The build custom simulated CPU inside the application features the following BRANCH PREDICTORS:
<br/>-<b>Static NOT-TAKEN</b>  (always returns the response: not-taken. Tells the CPU that The jump will NOT be taken)
<br/>-<b>Static TAKEN</b>  (always returns the response: not-taken. Tells the CPU that The jump will NOT be taken)
<br/>-Dynamic 1-BIT (works by the following given finite-state automata)

![imga](https://i.imgur.com/0cSOYko.jpg)

<br/>-Dynamic 2-BIT (works by the following given finite-state automata)
![imgb](https://i.imgur.com/OC5Szpg.jpg)

<br/>

<br/><b>The data structures and components of the CPU are:</b>
<br/>Data memory(64k locations)
<br/>General-purpose registers: 15 general registers(R0-R14)(8bit each)
<br/>Program Memory(supports up to 10.000 instructions)
<br/>FLAGS: 4 flags (Z,C,N,O)


---



# Prequisites
<br>PHP 5.5+
<br>XAMPP - Apache(for local webserver) or hosted web-server is fine.
<br>App will be started from index.php file (eg. http://localhost/cpuBP/index.php)


----



# Instructions on use 
<br>
<b>Please refer to Pages/help.html , which contains all the details of the language and the instructions .</b>
 <br/>
 
<b>cpuBP ASM instructions(help about them in Pages/help.html)(These are example of use, not proof of concept):</b>
 <br/>
JNZ 0xE300 <br>
JNZ to_label_jnz #it is easier to create labels inside JUMP instructions<br>
JPZ 0x0001 <br>
JPZ to_label_jpz<br>
eticheta_label_1:<br>
JMP 0x1111<br>
JMP _to_label #label in jump<br>
JNC 0x1000<br>
JNC to_label_jnc<br>
JPC 0x1001<br>
JPC to_label_jpc<br>
JNN 0x1002<br>
JNN to_label_jnn<br>
JPN 0x1003<br>
JPN to_label_jpn<br>
JNO 0x1004<br>
JNO to_label_jno<br>
JPO 0x1005<br>
JPO to_label_jpo<br>
_to_label:<br>
STR R3, R4, R5<br>
STR R6 , 0xff <br>
LDR R7, R8, R9<br>
LDR R10, 0x55<br>
ADD R11, R12<br>
ADD R13, 0xA<br>
ADC R14, R0<br>
SUB R0, R1<br>
SUB R0, 0x6<br>
SBC R1, R2<br>
AND R0, R1<br>
ORR R0,R1<br>
XOR R0,R1<br>
CMP R10,R11<br>
INV R13<br>
SHL R13<br>
SHR R13<br>
ROL R13<br>
ROR R13<br>
PSH R7<br>
POP R7<br>
CLZ<br>
CLC<br>
CLN<br>
NOP   <br>
HALT <br>

----



# Results 
<br> Interface explained:
<!-- ... -->
<!-- COMENTARIU: imagine cu interfata explicata cpuBP comes here... -->
![img1](https://i.imgur.com/wQQiOZr.jpg)

<br />
<br />Tests:
<br />

<br /><b>A.-Running the application using - compute array sum</b>

![img2](https://i.imgur.com/X40ZFeQ.jpg)


<br /><b>B.-Running the application using - compute max Element of array</b>

![img3](https://i.imgur.com/90HsFF6.jpg)


<br /><b>C.-Running the application using - BRANCH PREDICTION on quicksort ASM algorithm</b>

![img4](https://i.imgur.com/7WSzLkW.jpg)

<br /> The example result of bubble sort algorithm can be found in public/test_and_resources . Can be tried by yourself.

<br />
<br />



