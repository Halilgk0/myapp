<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfoNotificationController extends Controller
{
    private const ADMIN_MESSAGES = [
        ['title' => 'Sistem Notu', 'message' => 'Operasyon panelindeki günlük verileri kontrol etmeyi unutmayın.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Bugünkü turlara atanmamış bilet var mı diye Operasyonlar sayfasından kontrol edebilirsiniz.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Bekleyen bilet taleplerini gözden geçirmeyi unutmayın.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Araç ve şoför atamalarını güncel tutmak sahadaki karışıklıkları önler.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Döviz kurlarını güncel tutmak muhasebe kayıtlarının doğruluğunu artırır.', 'tone' => 'info'],
        ['title' => 'İyi çalışmalar', 'message' => 'Günün operasyonu için bilet ve araç atamalarını gözden geçirebilirsiniz.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Bugün de misafirlerinize unutulmaz bir deneyim sunmaya bir adım daha yaklaştınız.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Her doğru atama sahada bir sorunu daha önlemiş olur, iyi gidiyorsunuz.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Küçük düzenlemeler büyük operasyonel farklar yaratır.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Ekibinizin bugünkü emeği yarının iyi yorumlarına dönüşecek.', 'tone' => 'success'],
        ['title' => 'Günün Sözü', 'message' => 'Düzen, özgürlüğün ilk şartıdır.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'İyi bir plan, bugün uygulanan mükemmel bir plandan daha değerlidir.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'Küçük adımlar büyük yolculukları tamamlar.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'Sakin bir zihin en karmaşık günü bile yönetebilir.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'Bugün attığınız her doğru adım, yarının güvenini inşa eder.', 'tone' => 'quote'],
        ['title' => 'İpucu', 'message' => 'Daha iyi bir görüntüleme deneyimi için karanlık temayı deneyebilirsiniz.', 'tone' => 'info'],
    ];

    private const AGENCY_MESSAGES = [
        ['title' => 'Sistem Notu', 'message' => 'Paylaşılan turları ve bekleyen bilet taleplerini kontrol edebilirsiniz.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'İade edilen taleplerinizi düzenleyip tekrar gönderebilirsiniz.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Mutabakat bekleyen işlemleriniz varsa Muhasebe sayfasından onaylayabilirsiniz.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Güncel döviz kurlarını kontrol ederek satış fiyatlarınızı doğru belirleyebilirsiniz.', 'tone' => 'info'],
        ['title' => 'Sistem Notu', 'message' => 'Yeni paylaşılan turlar olup olmadığını kontrol etmeyi unutmayın.', 'tone' => 'info'],
        ['title' => 'İyi çalışmalar', 'message' => 'Güncel rezervasyonlarınızı ve müşteri bilgilerinizi düzenli kontrol edin.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Her satış, misafire güzel bir tatilin ilk adımını sunuyor.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Güvenilir bir acente olmak zamanında ve doğru bilgiyle başlar, iyi gidiyorsunuz.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Bugünkü emeğiniz, yarınki tekrar tercih edilme sebebiniz olacak.', 'tone' => 'success'],
        ['title' => 'İyi çalışmalar', 'message' => 'Her doğru bilgilendirme müşteri memnuniyetine bir adım daha yaklaştırır.', 'tone' => 'success'],
        ['title' => 'Günün Sözü', 'message' => 'Güven, küçük sözlerin tutulmasıyla inşa edilir.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'İyi iletişim, çoğu sorunu daha başlamadan çözer.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'Küçük detaylara gösterilen özen büyük güveni kazandırır.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'Sabır ve dikkat en karmaşık talebi bile kolaylaştırır.', 'tone' => 'quote'],
        ['title' => 'Günün Sözü', 'message' => 'Bugün gösterdiğiniz özen, yarının en iyi yorumu olur.', 'tone' => 'quote'],
        ['title' => 'İpucu', 'message' => 'Daha iyi bir görüntüleme deneyimi için karanlık temayı deneyebilirsiniz.', 'tone' => 'info'],
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $messages = $request->user()?->isAdmin() ? self::ADMIN_MESSAGES : self::AGENCY_MESSAGES;
        $note = $messages[array_rand($messages)];

        return response()->json([
            'title' => __($note['title']),
            'message' => __($note['message']),
            'tone' => $note['tone'],
        ]);
    }
}
