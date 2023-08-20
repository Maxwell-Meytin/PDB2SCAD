import sys

#PDB File Keys
keyA="ATOM"
keyB="ENDMDL"
keyC="TER"

#Acidic Acids
key1="ASP"
key2="GLU"

#Basic Acids
key3="GLN"
key4="LYS"
key5="GLY"
key6="HIS"
key7="ARG"

#Neutral Acids
key8="CYS"
key9="SER"
key10="ILE"
key11="PRO"
key12="THR"
key13="PHE"
key14="ASN"
key15="LEU"
key16="TRP"
key17="ALA"
key18="VAL"
key19="TYR"
key20="MET"


lineIndex=""
tempX=0
lastX=0
tempY=0
lastY=0
tempZ=0
lastZ=0
atomCount=0
cPos=""
lIndex=0
output_location="/var/www/html/pdb2scad/output/"
input_location="/var/www/html/pdb2scad/input/"
#protien=input("Protien Name?")
#protien="1a11"
n=len(sys.argv)

if n<2:
	print("pdb file is required")
else:

    protien=sys.argv[1]
    f = open(input_location + protien,"r")
    fOut=open(output_location + "output_" + protien.rstrip(".pdb") + ".scad","w")
    fOut.write("$fa=16;\n$fs=0.25;\n\nmodule cylinderPath(segSize,points)\n{\n  for(p=[0:len(points)-2])\n  {\n    startPoint=points[p];\n    endPoint=points[p+1];\n\n    dx=endPoint[0]-startPoint[0];\n    dy=endPoint[1]-startPoint[1];\n    dz=endPoint[2]-startPoint[2];\n\n    r=ceil(sqrt(dx^2+dy^2+dz^2));\n\n    a1=dx==0?90:atan(dy/dx);\n    a2=dz==0?90:atan(sqrt(dx^2+dy^2)/dz);\n\n\n    translate(startPoint)\n    {\n      if(dx<0 && dz<0)\n      {\n        rotate([0,0,a1])\n        rotate([0,180-a2,0])\n        cylinder(d=segSize,h=r);\n      }\n      else if(dx<0 && dz>=0)\n      {\n        rotate([0,0,a1])\n        rotate([0,-a2,0])\n        cylinder(d=segSize,h=r);\n      }\n      else if(dx>=0 && dz<0)\n      {\n        rotate([0,0,a1])\n        rotate([0,a2+180,0])\n        cylinder(d=segSize,h=r);\n      }\n      else if(dx>=0 && dz>=0)\n      {\n        rotate([0,0,a1])\n        rotate([0,a2,0])\n        cylinder(d=segSize,h=r);\n      }\n    }\n  }\n}\n")
    for line in f:
        if line.startswith(keyA):
            if lineIndex=="":
                lineIndex=int(line[22:26])
                
            if int(line[22:26])==lineIndex:
                atomCount+=1
                tempX+=float(line[30:38])
                tempY+=float(line[38:46])
                tempZ+=float(line[46:54])
                
            else:
                if atomCount==0:
                    finX=round(tempX/1,3)
                    finY=round(tempY/1,3)
                    finZ=round(tempZ/1,3)
                    
                else:
                    finX=round(tempX/atomCount,3)
                    finY=round(tempY/atomCount,3)
                    finZ=round(tempZ/atomCount,3)
                    
                print("got here")
                if str(line[17:20])==key1 or str(line[17:20])==key2:
                    fOut.write("translate(["+str(round(finX,3))+","+str(round(finY,3))+","+str(round(finZ,3))+"])\n"+"  sphere(r=2);\n")

                elif str(line[17:20])==key3 or str(line[17:20])==key4 or str(line[17:20])==key5 or str(line[17:20])==key6 or str(line[17:20])==key7:
                    fOut.write("translate(["+str(round(finX,3))+","+str(round(finY,3))+","+str(round(finZ,3))+"])\n"+"  cube(size=3, center=true);\n")

                else:
                    fOut.write("translate(["+str(round(finX,3))+","+str(round(finY,3))+","+str(round(finZ,3))+"])\n"+"scale([2.25,2.25,2.25])\n"+"polyhedron(points=[[1,0,0],[-1,0,0],[0,1,0],[0,-1,0],[0,0,1],[0,0,-1]], faces=[[2,4,1],[1,4,3],[3,4,0],[0,4,2],[5,0,2],[5,2,1],[5,1,3],[5,3,0]]);\n")
                
                if lIndex==0:
                    cPos+=("["+str(round(finX,3))+","+str(round(finY,3))+","+str(round(finZ,3))+"]\n")
                    lIndex+=1
                    
                else:
                    cPos+=(",["+str(round(finX,3))+","+str(round(finY,3))+","+str(round(finZ,3))+"]\n")

                print("Acid "+str(lineIndex)+" is done")
                print(finX,finY,finZ)
                tempX=0
                tempY=0
                tempZ=0
                lineIndex = int(line[22:26])
                atomCount=1
                print(line[30:38])
                tempX+=float(line[30:38])
                tempY+=float(line[38:46])
                tempZ+=float(line[46:54])
                
        elif line.startswith(keyB) or line.startswith(keyC):
            finX=round(tempX/atomCount,3)
            finY=round(tempY/atomCount,3)
            finZ=round(tempZ/atomCount,3)
            print("Acid "+str(lineIndex)+" is done")
            print(finX,finY,finZ)
            fOut.write("translate(["+str(round(finX,3))+","+str(round(finY,3))+","+str(round(finZ,3))+"])\n"+"  sphere(r=2);\n")
            cPos+=(",["+str(round(finX,5))+","+str(round(finY,3))+","+str(round(finZ,3))+"]")
            print(cPos)
            fOut.write("cylinderPath(segSize=1.5,points=["+cPos+"]);")
            tempX=0
            tempY=0
            tempZ=0
            print("Finished")
            break


    fOut.close()
    f.close()
