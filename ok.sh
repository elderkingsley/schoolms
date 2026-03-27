#!/usr/bin/env bash

# Check if a message was provided
if [ -z "$1" ]
then
  echo "❌ Error: Please provide a commit message."
  echo "Usage: ./ok.sh 'your message here'"
  exit 1
fi

echo "🚀 Starting Git Push..."

# 1. Add all changes
git add .

# 2. Commit with the message provided in the command line
git commit -m "$1"

# 3. Push to the main branch
git push origin main

echo "✅ Done! Your code is on its way to GitHub."
