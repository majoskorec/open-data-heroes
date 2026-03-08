<?php

declare(strict_types=1);

namespace App\OpenDataAnalyzer;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class OpenDataAnalyzer
{
    public function __construct(
        #[Autowire(service: 'ai.agent.open_data_source_analyzer')]
        private readonly AgentInterface $agent,
    ) {
    }

    public function prompt(string $prompt): string
    {
        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT

PROMPT),
            Message::ofUser($prompt),
        );

        return $this->agent->call($messages)->getContent();
    }

    public function calculateAssetDeclarationDiff(string $ancestorJson, string $descendantJson): string
    {
        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT
Si matcher majetkových položiek medzi dvoma rokmi majetkového priznania.

Tvojou úlohou je porovnať iba tieto 2 sekcie:
- declarationRealEstate
- declarationMovableAssets

Dostaneš JSON pre nižší rok a JSON pre vyšší rok.
Každá položka obsahuje aj `id`.

Tvoj cieľ:
1. Spárovať položky z nižšieho roku s položkami z vyššieho roku.
2. Vrátiť presne jeden validný JSON objekt bez markdownu, bez komentárov, bez vysvetlení a bez akéhokoľvek textu navyše.
3. Výstup musí obsahovať iba diff položky pre:
   - nehnuteľnosti
   - hnuteľné veci

Vráť presne túto JSON štruktúru:

{
  "realEstateDiffs": [
    {
      "fromId": null,
      "toId": null
    }
  ],
  "movableAssetDiffs": [
    {
      "fromId": null,
      "toId": null
    }
  ]
}

Pravidlá:
1. Vráť iba validný JSON.
2. Nepoužívaj ```json ani markdown.
3. Nepoužívaj žiadne ďalšie polia.
4. Každá položka z nižšieho aj vyššieho roku sa môže použiť najviac raz.
5. Ak položka z nižšieho roku má jasný pár vo vyššom roku, vytvor diff:
   - `fromId` = id z nižšieho roku
   - `toId` = id z vyššieho roku
6. Ak položka z nižšieho roku nemá pár vo vyššom roku, vytvor diff:
   - `fromId` = id z nižšieho roku
   - `toId` = null
7. Ak položka z vyššieho roku nemá pár v nižšom roku, vytvor diff:
   - `fromId` = null
   - `toId` = id z vyššieho roku
8. `shareDiff` vždy nastav na null. Túto hodnotu dopočíta aplikácia.
9. Nepáruj položky len podľa poradia v zozname.
10. Ak si nie si istý, buď konzervatívny a radšej nechaj položku ako unmatched.
11. Výstupné diffy majú obsahovať kompletné pokrytie všetkých položiek v oboch rokoch:
   - každá položka z nižšieho roku musí byť buď spárovaná, alebo unmatched
   - každá položka z vyššieho roku musí byť buď spárovaná, alebo unmatched

Pravidlá párovania pre declarationRealEstate:
1. Primárne páruj podľa:
   - assetType
   - cadastralArea
   - lvNumber
2. Ownership share nepoužívaj ako hlavné kritérium párovania.
3. rawText môžeš použiť ako pomocné kritérium.
4. Ak assetType, cadastralArea a lvNumber zodpovedajú, ide spravidla o ten istý asset aj keď sa podiel zmenil.
5. Ak je LV číslo rovnaké, ale assetType odlišné (napr. BYT vs GARÁŽ), nepáruj ich.

Pravidlá párovania pre declarationMovableAssets:
1. Primárne páruj podľa:
   - assetType
   - brand
   - manufactureYear
2. rawText môžeš použiť ako pomocné kritérium.
3. Menšie rozdiely v rawText, interpunkcii alebo medzerách ignoruj, ak ostatné kľúčové údaje sedia.
4. Ak assetType, brand a manufactureYear zodpovedajú, ide spravidla o ten istý asset aj keď sa podiel zmenil.
5. Ak brand alebo manufactureYear chýba pri oboch položkách, môžeš párovať podľa assetType a rawText len ak je zhoda veľmi silná.
6. Pri všeobecných položkách ako "OSOBNÝ MAJETOK" alebo "ZARIADENIE DOMÁCNOSTI" buď opatrný, ale ak sa zhoduje assetType a ostatné dostupné údaje, môžeš ich spárovať.

Dôležité:
- Porovnávaš iba declarationRealEstate a declarationMovableAssets.
- declarationLiabilities a declarationValuableRight ignoruj.
- Každý pár vytvor len raz.
- Výstup má byť deterministický a konzervatívny.
PROMPT),
            Message::ofUser(<<<PROMPT
Porovnaj majetkové položky medzi dvoma rokmi.

Nižší rok:
{$ancestorJson}

Vyšší rok:
{$descendantJson}
PROMPT),
        );

        return $this->agent->call($messages)->getContent();
    }

    public function evaluateMovableAsset(string $input, DatePoint $valuationDate): string
    {
        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT
Si extraktor a odhadca hodnoty hnuteľného majetku pre dátový prototyp majetkových priznaní.

Tvojou úlohou je z poskytnutých údajov o jednej hnuteľnej veci vrátiť presne jeden validný JSON objekt bez markdownu, bez komentárov, bez vysvetlení a bez akéhokoľvek dodatočného textu.

Výstup musí byť vhodný na deserializáciu do PHP entity MovableAssetValuation.

Vráť presne túto JSON štruktúru:

{
  "sourceType": null,
  "currency": "EUR",
  "exactValue": null,
  "estimatedMinValue": null,
  "estimatedLikelyValue": null,
  "estimatedMaxValue": null
}

Pravidlá:
1. Vráť iba validný JSON objekt.
2. Nepoužívaj markdown ani bloky ```json.
3. Nepoužívaj žiadne ďalšie polia navyše.
4. Všetky peňažné hodnoty vracaj ako string s dvoma desatinnými miestami a bodkou, napr. "12500.00".
5. Menu vždy nastav na "EUR", ak nie je výslovne uvedené inak.
6. Ak je v zadaní spoľahlivo uvedená presná hodnota hnuteľnej veci, vyplň:
   - "sourceType": "declared" alebo "external"
   - "exactValue": ...
   - všetky estimated hodnoty nastav na null
7. Ak presná hodnota nie je známa, vytvor konzervatívny odhad a vyplň:
   - "sourceType": "ai"
   - "exactValue": null
   - "estimatedMinValue"
   - "estimatedLikelyValue"
   - "estimatedMaxValue"
8. Odhad musí spĺňať:
   - min <= likely <= max
   - všetky tri hodnoty musia byť realistické a konzistentné
9. Ak nemáš dostatok údajov ani na hrubý odhad, vráť:
   - "sourceType": "ai"
   - "exactValue": null
   - všetky estimated hodnoty null
10. Buď konzervatívny. Nevymýšľaj si presnú hodnotu, ak ju nevieš.
11. Odhaduj hodnotu výhradne ku dátumu uvedenému v poli `valuationDate`.
12. Ak je `valuationDate` v minulosti, neodhaduj dnešnú cenu, ale cenu realistickú pre daný dátum.
13. Nezohľadňuj vývoj cien po dátume `valuationDate`.
14. Ak je uvedený spoluvlastnícky podiel, oceňuj iba hodnotu patriacu deklarovanému podielu, nie celej veci.
15. Pri odhade zohľadni len údaje, ktoré sú reálne dostupné zo vstupu, najmä:
   - druh hnuteľnej veci
   - značku alebo model
   - rok výroby
   - spoluvlastnícky podiel
   - prípadnú zberateľskú alebo historickú povahu veci
16. Pri vozidlách zohľadni vek veci vzhľadom na `valuationDate`, nie vzhľadom na dnešok.
17. Pri veteránoch, zberateľských motocykloch alebo neobvyklých značkách buď opatrný a radšej použi širší interval.
18. Pri všeobecných kategóriách ako "OSOBNÝ MAJETOK" alebo "ZARIADENIE DOMÁCNOSTI" bez ďalšej špecifikácie použi len veľmi hrubý a konzervatívny odhad, alebo null, ak nie je dostatok údajov.
19. Nikdy nepridávaj textové zdôvodnenie. Vráť iba JSON.

Interpretácia sourceType:
- "declared" = presná hodnota je výslovne v zadaní
- "external" = presná hodnota je dodaná z externého zdroja v zadaní
- "ai" = ide o odhad

Príklady interpretácie:
- AUTOMOBIL + značka + rok výroby = urob odhad podľa typu vozidla, veku a značky
- MOTOCYKEL + značka + rok výroby = urob odhad podľa typu motocykla, veku a značky
- OSOBNÝ MAJETOK bez detailu = iba veľmi hrubý odhad alebo null
- ZARIADENIE DOMÁCNOSTI bez detailu = iba veľmi hrubý odhad alebo null

Ak si nie si istý, buď konzervatívny a použi null.
PROMPT),
            Message::ofUser(<<<PROMPT
Oceň nasledujúcu hnuteľnú vec ku zadanému dátumu.

valuationDate: {$valuationDate->format('Y-m-d')}

movableAsset:
{$input}
PROMPT),
        );

        return $this->agent->call($messages)->getContent();
    }

    public function evaluateRealEstate(string $input, DatePoint $valuationDate): string
    {
        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT
Si extraktor a odhadca hodnoty nehnuteľnosti pre dátový prototyp majetkových priznaní.

Tvojou úlohou je z poskytnutých údajov o jednej nehnuteľnosti vrátiť presne jeden validný JSON objekt bez markdownu, bez komentárov, bez vysvetlení a bez akéhokoľvek dodatočného textu.

Výstup musí byť vhodný na deserializáciu do PHP entity RealEstateValuation.

Vráť presne túto JSON štruktúru:

{
  "sourceType": null,
  "currency": "EUR",
  "exactValue": null,
  "estimatedMinValue": null,
  "estimatedLikelyValue": null,
  "estimatedMaxValue": null
}

Pravidlá:
1. Vráť iba validný JSON objekt.
2. Nepoužívaj markdown ani bloky ```json.
3. Nepoužívaj žiadne ďalšie polia navyše.
4. Všetky peňažné hodnoty vracaj ako string s dvoma desatinnými miestami a bodkou, napr. "125000.00".
5. Menu vždy nastav na "EUR", ak nie je výslovne uvedené inak.
6. Ak je v zadaní spoľahlivo uvedená presná hodnota nehnuteľnosti, vyplň:
   - "sourceType": "declared" alebo "external"
   - "exactValue": ...
   - všetky estimated hodnoty nastav na null
7. Ak presná hodnota nie je známa, vytvor konzervatívny odhad a vyplň:
   - "sourceType": "ai"
   - "exactValue": null
   - "estimatedMinValue"
   - "estimatedLikelyValue"
   - "estimatedMaxValue"
8. Odhad musí spĺňať:
   - min <= likely <= max
   - všetky tri hodnoty musia byť realistické a konzistentné
9. Ak nemáš dostatok údajov ani na hrubý odhad, vráť:
   - "sourceType": "ai"
   - "exactValue": null
   - všetky estimated hodnoty null
10. Buď konzervatívny. Nevymýšľaj si presnú hodnotu, ak ju nevieš.
11. Pri odhade zohľadni len údaje, ktoré sú reálne dostupné zo vstupu, najmä:
   - typ nehnuteľnosti
   - lokalitu / katastrálne územie
   - prípadne list vlastníctva, ak nepriamo pomáha s lokalitou
   - spoluvlastnícky podiel
12. Ak je uvedený spoluvlastnícky podiel, oceňuj iba hodnotu patriacu deklarovanému podielu, nie celej nehnuteľnosti.
13. Ak lokalita výrazne zvyšuje alebo znižuje hodnotu, zohľadni to v odhade.
14. Ak ide o nejasný typ nehnuteľnosti alebo chýba lokalita, odhad má byť širší a opatrnejší.
15. Nikdy nepridávaj textové zdôvodnenie. Vráť iba JSON.
16. Odhaduj hodnotu výhradne ku dátumu uvedenému v poli `valuationDate`.
17. Ak je `valuationDate` v minulosti, neodhaduj dnešnú cenu, ale cenu realistickú pre daný dátum.
18. Ak je `valuationDate` neznámy alebo chýba, použi konzervatívny odhad a ber ho ako neurčitý časový kontext.
19. Nezohľadňuj vývoj cien po dátume `valuationDate`.

Interpretácia sourceType:
- "declared" = presná hodnota je výslovne v zadaní
- "external" = presná hodnota je dodaná z externého zdroja v zadaní
- "ai" = ide o odhad
PROMPT),
            Message::ofUser(<<<PROMT
Oceň nasledujúcu nehnuteľnosť ku zadanému dátumu.

valuationDate: {$valuationDate->format('Y-m-d')}

realEstate:
{$input}
PROMT),
        );

        return $this->agent->call($messages)->getContent();
    }

    public function parseRawInputToParsedAssetDeclarationDtoJson(string $input): string
    {
        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT
Si extraktor štruktúrovaných dát z majetkových priznaní verejných funkcionárov.

Tvojou úlohou je z textového vstupu vyrobiť presne jeden validný JSON objekt, bez markdownu, bez komentárov, bez vysvetlení a bez akéhokoľvek dodatočného textu.

Výstup musí byť vhodný na deserializáciu do PHP DTO objektu ParsedAssetDeclarationDto.

Dôležité pravidlá:
1. Vráť iba jeden validný JSON objekt.
2. Nepoužívaj ```json, markdown ani žiadny text navyše.
3. Nepoužívaj žiadne ďalšie polia mimo povolených.
4. Ak hodnota v texte chýba alebo ju nevieš spoľahlivo určiť, použi null.
5. Zachovaj diakritiku.
6. Text neprekladaj.
7. Pri zoznamových sekciách zachovaj poradie položiek.
8. Do polí `rawText` ukladaj pôvodný riadok alebo pôvodný text danej položky/sekcie.
9. Sekcie typu „nevykonávam“, „neužívam“, „žiadne“ mapuj na objekt so `declaredNone: true` a `rawText` nastav na pôvodnú hodnotu.
10. Ak sekcia v texte vôbec nie je prítomná, nastav ju na null.
11. Sumy vracaj ako string v desatinnom formáte s bodkou, napr. "62925.00".
12. Dátumy pre `originatedAt` vracaj vo formáte YYYY-MM-DD. Ak dátum nevieš spoľahlivo previesť, použi null.
13. Číselné polia vracaj ako čísla, nie ako stringy.
14. Boolean polia vracaj ako true/false.
15. Nikdy nevracaj text namiesto objektu tam, kde sa očakáva objekt.
16. Nikdy nevracaj null namiesto array. Ak nie sú žiadne položky, vráť prázdne pole [].
17. Všetky ownership share hodnoty vracaj ako objekt `ownershipShare` s poľami:
    - `numerator`
    - `denominator`
18. Ak ownership share nevieš spoľahlivo určiť, nastav `ownershipShare` na null.
19. Ak je ownership share zapísaný ako zlomok, napr. `1/2`, mapuj ho na:
    - `"ownershipShare": { "numerator": 1, "denominator": 2 }`
20. Ak je ownership share zapísaný ako percento, napr. `50%`, mapuj ho na zlomok v základnom tvare:
    - `50%` -> `1/2`
    - `100%` -> `1/1`
    - `25%` -> `1/4`
21. Ak je ownership share zapísaný ako celé číslo `1`, mapuj ho na:
    - `"ownershipShare": { "numerator": 1, "denominator": 1 }`

Povolená JSON štruktúra je presne táto:

{
  "publicFunctions": [
    {
      "name": null,
      "rawText": null
    }
  ],
  "income": {
    "publicFunctionIncome": null,
    "otherIncome": null
  },
  "employmentStatus": {
    "declaredNone": null,
    "rawText": null
  },
  "businessStatus": {
    "declaredNone": null,
    "rawText": null
  },
  "otherFunctionStatus": {
    "declaredNone": null,
    "rawText": null
  },
  "realEstates": [
    {
      "assetType": null,
      "cadastralArea": null,
      "lvNumber": null,
      "ownershipShare": {
        "numerator": null,
        "denominator": null
      },
      "rawText": null
    }
  ],
  "movableAssets": [
    {
      "assetType": null,
      "brand": null,
      "manufactureYear": null,
      "ownershipShare": {
        "numerator": null,
        "denominator": null
      },
      "rawText": null
    }
  ],
  "valuableRights": [
    {
      "assetType": null,
      "ownershipShare": {
        "numerator": null,
        "denominator": null
      },
      "rawText": null
    }
  ],
  "liabilities": [
    {
      "liabilityType": null,
      "ownershipShare": {
        "numerator": null,
        "denominator": null
      },
      "originatedAt": null,
      "rawText": null
    }
  ],
  "foreignRealEstateUsageStatus": {
    "declaredNone": null,
    "rawText": null
  },
  "foreignMovableUsageStatus": {
    "declaredNone": null,
    "rawText": null
  },
  "giftStatus": {
    "declaredNone": null,
    "rawText": null
  }
}

Mapovanie sekcií:
- "vykonávaná verejná funkcia" -> publicFunctions
- "príjmy za rok ..." -> income
- "vykonávam nasledovné zamestnanie ..." -> employmentStatus
- "vykonávam nasledovnú podnikateľskú činnosť ..." -> businessStatus
- "počas výkonu verejnej funkcie mám tieto funkcie ..." -> otherFunctionStatus
- "vlastníctvo nehnuteľnej veci" -> realEstates
- "vlastníctvo hnuteľnej veci" -> movableAssets
- "vlastníctvo majetkového práva alebo inej majetkovej hodnoty" -> valuableRights
- "existencia záväzku" -> liabilities
- "užívanie nehnuteľnej veci vo vlastníctve inej ..." -> foreignRealEstateUsageStatus
- "užívanie hnuteľnej veci vo vlastníctve inej ..." -> foreignMovableUsageStatus
- "prijaté dary alebo iné výhody" -> giftStatus

Pravidlá pre jednotlivé položky:

A. publicFunctions
- Každý riadok jednej funkcie je jeden objekt.
- `name` je celý text riadku funkcie.
- `rawText` je pôvodný text riadku.

B. income
- Z textu typu `62925 € (z výkonu verejnej funkcie), 722240 € (iné)` vyrob:
  - `publicFunctionIncome`: "62925.00"
  - `otherIncome`: "722240.00"

C. realEstates
- Z textu typu `BYT; kat. územie BRATISLAVA - STARÉ MESTO; číslo LV: 6227; podiel: 1/2` vyrob:
  - `assetType`: "BYT"
  - `cadastralArea`: "BRATISLAVA - STARÉ MESTO"
  - `lvNumber`: "6227"
  - `ownershipShare`: { "numerator": 1, "denominator": 2 }

D. movableAssets
- Z textu typu `AUTOMOBIL, továrenská značka: AUDI, rok výroby: 2008, podiel: 1/1` vyrob:
  - `assetType`: "AUTOMOBIL"
  - `brand`: "AUDI"
  - `manufactureYear`: 2008
  - `ownershipShare`: { "numerator": 1, "denominator": 1 }
- Ak značka alebo rok výroby chýba, použi null.

E. valuableRights
- Z textu typu `CENNÉ PAPIERE, podiel: 1/2` vyrob:
  - `assetType`: "CENNÉ PAPIERE"
  - `ownershipShare`: { "numerator": 1, "denominator": 2 }

F. liabilities
- Z textu typu `ÚVER, podiel: 1/1, dátum vzniku: 25. 06. 2008` vyrob:
  - `liabilityType`: "ÚVER"
  - `ownershipShare`: { "numerator": 1, "denominator": 1 }
  - `originatedAt`: "2008-06-25"

Ak si nie si istý, buď konzervatívny a použij null.
PROMPT),
            Message::ofUser(<<<PROMPT
Rozparsuj nasledujúci text do požadovaného JSON formátu.

Text vstupu:
{$input}
PROMPT
            ),
        );

        return $this->agent->call($messages)->getContent();
    }

    public function parseRawInputToAnnouncementParserDtoJson(string $input): string
    {
        $messages = new MessageBag(
            Message::forSystem(<<<PROMPT
Si extraktor štruktúrovaných dát.

Vráť presne jeden validný JSON objekt bez markdownu, bez komentárov a bez vysvetlení.

Výstup musí mať presne túto štruktúru:
{
  "publicOfficial": {
    "titleBefore": null,
    "firstName": null,
    "lastName": null,
    "titleAfter": null
  },
  "assetDeclaration": {
    "rawInput": "",
    "year": null,
    "externalId": null
  }
}

Pravidlá:
1. Vráť iba JSON. Nepoužívaj ```json ani iný text navyše.
2. Zachovaj názvy kľúčov presne:
   - publicOfficial.titleBefore
   - publicOfficial.firstName
   - publicOfficial.lastName
   - publicOfficial.titleAfter
   - assetDeclaration.rawInput
   - assetDeclaration.year
   - assetDeclaration.externalId
3. assetDeclaration.rawInput musí obsahovať celý pôvodný vstup bez straty obsahu.
4. year vyplň ako integer z hodnoty "oznámenie za rok".
5. externalId vyplň ako integer z hodnoty "ID oznámenia".
6. Meno rozparsuj takto:
   - titleBefore = tituly pred menom, napr. "JUDr."
   - firstName = krstné meno
   - lastName = priezvisko
   - titleAfter = tituly za menom
7. Ak nejaká hodnota v texte chýba alebo ju nevieš spoľahlivo určiť, použi null.
8. Neprevádzaj text na iný jazyk.
9. Nepridávaj žiadne ďalšie polia.
10. Ak je v mene viac titulov, spoj ich do jedného stringu oddeleného medzerou.
11. Diakritiku zachovaj.
12. Ak si nie si istý menom, priezviskom alebo titulom, neodhaduj a použi null.
PROMPT),
            Message::ofUser($input),
        );

        return $this->agent->call($messages)->getContent();
    }
}
