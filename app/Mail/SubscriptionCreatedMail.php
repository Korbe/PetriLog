<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;
use Illuminate\Queue\SerializesModels;

class SubscriptionCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function build()
    {
        return $this
            ->subject("🎉 Dein Abo ist aktiv!")
            ->view('emails.default')
            ->with([
                'name' => $this->user->name,
                'content' => new HtmlString('<p>🎉 <strong>Vielen Dank für das Abonnieren von PetriLog!</strong> Wir freuen uns sehr, dich an Bord zu haben.</p>
<p>Mit deinem Abonnement erhältst du Zugriff auf <strong>alle Funktionen von PetriLog</strong> und kannst so viel loggen und hochladen, wie du willst. 📝✨</p>
<p>Wenn du Fragen oder Anregungen hast, zögere bitte nicht, uns zu kontaktieren. 💬 Schreibe einfach eine E-Mail an <a href="mailto:info@petrilog.com">info@petrilog.com</a>. Wir sind immer hier, um dir zu helfen!</p>
<p>Nochmals vielen Dank für dein Vertrauen in PetriLog. Wir freuen uns darauf, dir ein großartiges Erlebnis zu bieten! 🚀</p>
<p style="text-align:center; padding-top:10px;padding-bottom:10px;">
  <a href="https://petrilog.com/dashboard" style="background-color:#118DF0;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;font-weight:bold;">Zu deinem Dashboard</a>
</p>'),
            ]);
    }
}
