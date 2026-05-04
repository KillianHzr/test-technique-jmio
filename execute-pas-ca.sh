#!/bin/bash

cols=$(tput cols)

train=(
"      ____      "
"  ___/____\\___  "
" |  _  __   _  |"
" |_/ |__| |__| \\|"
"    (O)   (O)   "
)

train_width=20

for (( pos=0; pos<cols+train_width; pos++ )); do
    clear
    for ligne in "${train[@]}"; dos
        printf "%${pos}s%s\n" "" "$ligne"
    done
    sleep 0.1
done
