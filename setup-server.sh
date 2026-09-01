#!/bin/bash
set -e

DOMAIN="irrigation.zegemenim.com.tr"

echo "🔧 Sunucu yapılandırması başlatılıyor..."

# 1. Nginx ve Certbot kurulumu (Eğer yoksa)
echo "📦 Gerekli paketler yükleniyor (Nginx, Certbot)..."
sudo apt update
sudo apt install -y nginx certbot python3-certbot-nginx

# 2. Nginx ayar dosyasını oluştur
echo "⚙️ Nginx ayarları $DOMAIN için yapılıyor..."
sudo bash -c "cat > /etc/nginx/sites-available/irrigation << 'EOF'
server {
    listen 80;
    server_name $DOMAIN;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF"

# 3. Nginx'i aktifleştir
echo "🔗 Nginx yapılandırması aktifleştiriliyor..."
sudo ln -sf /etc/nginx/sites-available/irrigation /etc/nginx/sites-enabled/
# Varsayılan nginx sayfasını kaldır (çakışmaması için)
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

# 4. Let's Encrypt SSL Kurulumu
echo "🔒 SSL Sertifikası kuruluyor..."
sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos -m admin@zegemenim.com.tr || echo "⚠️ SSL kurulumu başarısız oldu. Manuel olarak 'sudo certbot --nginx -d $DOMAIN' çalıştırın."

echo "✅ Kurulum tamamlandı! Sitenize https://$DOMAIN adresinden ulaşabilirsiniz."
