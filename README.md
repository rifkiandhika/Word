Step 1 : Install Docker https://docs.docker.com/desktop/setup/install/windows-install/
Step 2 : Pindah folder Docker ini diluar project Laravel
Step 3 : Composer install jangan lupa, sesuain juga dengan versi Laravel nya, yang disini versi 10
Step 4 : Masukin ini ke file .env
         ONLYOFFICE_SERVER_URL=http://localhost:8081
         ONLYOFFICE_APP_URL=http://host.docker.internal:8000
         ONLYOFFICE_JWT_SECRET=<isi dari .env docker> (bisa langsung copas aja soalnya ini tabuatin uniquecode baru)
Step 5 : Install WSL di administrator PowerShell wsl --install kalo udah restart komputer/Laptop
Step 6 : di folder Docker ini install composer ini docker compose up -d
Step 7 : kalo udah masuk ke folder Docker buka powershell/cmd cek statusnya docker compose ps nah statusnya harus UP ya
Step 8 : kalo uda masuk ke web ini http://localhost:8081/healthcheck dan hasilnya harus true
NAH DAH kalo kurang paham tanya ai aja pasti ngerti, males ngetik panjang' euyy wkwkwk