#!/bin/bash
set -e

echo "🚀 Irrigation Sistemi Deployment Başlıyor..."

# 1. En güncel kodları çek
echo "📦 Güncel kodlar GitHub'dan çekiliyor..."
git pull origin main

# 2. Docker konteynerlerini durdur ve yeniden derle
echo "🐳 Docker konteynerleri yeniden inşa ediliyor..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d

echo "✅ Deployment başarıyla tamamlandı!"
echo "🔄 Temizlik yapılıyor..."
docker system prune -f

echo "🎉 Sistem şu an 8080 portunda çalışıyor."
