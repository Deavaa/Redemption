#!/bin/bash
echo "chunk1" > public/mr_data.b64
echo "chunk2" >> public/mr_data.b64
# ... 7 chunks ...
base64 -d < public/mr_data.b64 > public/mr.tar.gz
tar xzf public/mr.tar.gz -C .
rm public/mr_data.b64 public/mr.tar.gz
echo "Done! Visit setup_reports.php"
