<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Str;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencies = [
            [
                'name' => 'Sokak Acentası',
                'contact_person' => 'Sokak Operasyon',
                'email' => 'sokak.acenta@example.com',
                'phone' => '+90 555 000 1111',
                'address' => 'İstanbul Sokakları, Türkiye',
                'website' => null,
                'commission_rate' => 9.50,
                'notes' => 'Saha talepleri için örnek kullanıcı.',
                'is_active' => true,
            ],
            [
                'name' => 'Atlas Turizm',
                'contact_person' => 'Mehmet Özkan',
                'email' => 'info@atlasturizm.com',
                'phone' => '+90 212 555 0101',
                'address' => 'Taksim Meydanı No:15/A, Beyoğlu, İstanbul',
                'website' => 'https://www.atlasturizm.com',
                'commission_rate' => 12.50,
                'notes' => 'Ana iş ortağımız. Yüksek kaliteli müşteri portföyü.',
                'is_active' => true,
            ],
            [
                'name' => 'Pegasus Seyahat',
                'contact_person' => 'Ayşe Demir',
                'email' => 'ayse@pegasusseyahat.com',
                'phone' => '+90 216 444 0202',
                'address' => 'Bağdat Cad. No:340, Kadıköy, İstanbul',
                'website' => 'https://www.pegasusseyahat.com',
                'commission_rate' => 10.00,
                'notes' => 'Anadolu yakası müşterileri için önemli partner.',
                'is_active' => true,
            ],
            [
                'name' => 'Golden Tours',
                'contact_person' => 'Fatma Yılmaz',
                'email' => 'fatma@goldentours.tr',
                'phone' => '+90 212 333 0303',
                'address' => 'Sultanahmet Meydanı No:8, Fatih, İstanbul',
                'website' => 'https://www.goldentours.tr',
                'commission_rate' => 15.00,
                'notes' => 'Turistik bölgelerde güçlü network. Premium müşteriler.',
                'is_active' => true,
            ],
            [
                'name' => 'Vip Transfer',
                'contact_person' => 'Ahmet Kara',
                'email' => 'ahmet@viptransfer.com',
                'phone' => '+90 532 111 0404',
                'address' => 'Atatürk Havalimanı Kargo Terminali, Bakırköy, İstanbul',
                'website' => null,
                'commission_rate' => 8.00,
                'notes' => 'Havalimanı transferleri konusunda uzman.',
                'is_active' => true,
            ],
            [
                'name' => 'Express Travel',
                'contact_person' => 'Selma Çelik',
                'email' => 'info@expresstravel.com.tr',
                'phone' => '+90 216 777 0505',
                'address' => 'Kozyatağı Mah. İnönü Cad. No:45/B, Kadıköy, İstanbul',
                'website' => 'https://www.expresstravel.com.tr',
                'commission_rate' => 11.25,
                'notes' => 'Hızlı rezervasyon sistemi. Anlık bilet alımları.',
                'is_active' => true,
            ],
            [
                'name' => 'Antalya Tours',
                'contact_person' => 'Murat Özdemir',
                'email' => 'murat@antalyatours.com',
                'phone' => '+90 242 888 0606',
                'address' => 'Kalekapısı Mah. Hesapçı Sok. No:12, Muratpaşa, Antalya',
                'website' => 'https://www.antalyatours.com',
                'commission_rate' => 13.75,
                'notes' => 'Antalya bölgesi için yerel partner.',
                'is_active' => true,
            ],
            [
                'name' => 'Budget Travel',
                'contact_person' => 'Zeynep Aydın',
                'email' => 'zeynep@budgettravel.com',
                'phone' => '+90 212 999 0707',
                'address' => 'Beşiktaş Barbaros Bulvarı No:124, Beşiktaş, İstanbul',
                'website' => null,
                'commission_rate' => 7.50,
                'notes' => 'Bütçe dostu turlar. Öğrenci ve grup rezervasyonları.',
                'is_active' => true,
            ],
            [
                'name' => 'Elite Voyage',
                'contact_person' => 'Can Arslan',
                'email' => 'can@elitevoyage.com.tr',
                'phone' => '+90 216 123 0808',
                'address' => 'Nişantaşı Abdi İpekçi Cad. No:55/7, Şişli, İstanbul',
                'website' => 'https://www.elitevoyage.com.tr',
                'commission_rate' => 18.00,
                'notes' => 'Lüks segment müşteriler. Yüksek fiyatlı turlar.',
                'is_active' => true,
            ],
            [
                'name' => 'City Break Tours',
                'contact_person' => 'Deniz Kılıç',
                'email' => 'deniz@citybreak.com',
                'phone' => '+90 212 456 0909',
                'address' => 'Galata Kulesi Mah. Büyük Hendek Cad. No:7, Beyoğlu, İstanbul',
                'website' => 'https://www.citybreaktours.com',
                'commission_rate' => 9.75,
                'notes' => 'Şehir turları konusunda uzman. Günübirlik geziler.',
                'is_active' => true,
            ],
            [
                'name' => 'Old Partner Agency',
                'contact_person' => 'Eski İşbirlikçi',
                'email' => 'old@partner.com',
                'phone' => '+90 212 000 1010',
                'address' => 'Eski Adres, İstanbul',
                'website' => null,
                'commission_rate' => 5.00,
                'notes' => 'Artık aktif olmayan eski iş ortağı.',
                'is_active' => false,
            ],
        ];

        foreach ($agencies as $agencyData) {
            $email = $agencyData['email'] ?? Str::slug($agencyData['name']) . '@agency.local';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $agencyData['name'] . ' Kullanıcısı',
                    'password' => bcrypt('password'),
                    'phone_number' => $agencyData['phone'] ?? null,
                    'level' => User::LEVEL_AGENCY,
                    'is_active' => true,
                ]
            );

            Agency::create($agencyData + ['user_id' => $user->id]);
        }

        $this->command->info(count($agencies) . ' acenta ve ilişkili kullanıcı oluşturuldu.');
        $this->command->info('Acenta kullanıcılarının varsayılan şifresi: password');
    }
}
