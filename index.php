<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clan Messages</title>
</head>

<body>
    <p>既然都攤在陽光下了，與其被少數人利用，不如讓大家都能使用。</p>

    <span>公會 ID</span>
    <input type="text" id="c">
    <br>
    <span>歷史記錄筆數（32~1500）</span>
    <input type="number" id="n" value="1500" min="32" max="1500">
    <br>
    <input type="button" value="搜尋">

    <br>
    <hr><br>

    <div>結果</div>
    <div id="result"></div>

    <script>
        (() => {
            const result = document.querySelector("#result");
            const button = document.querySelector("input[type='button']");
            const c = document.querySelector("#c");
            const n = document.querySelector("#n");
            button.onclick = async _ => {
                const fd = new FormData();
                fd.append("c", c.value);
                fd.append("n", n.value);

                const response = await fetch("./api/cm.php", {
                    method: "POST",
                    body: fd
                }).then(
                    r => r.json()
                );

                if (response.success === true) {
                    let html = "<pre>";
                    response.data.reverse();

                    response.data.forEach(e => {
                        html += translate(e) + "<br>";
                    });

                    html += "</pre>";

                    result.innerHTML = html;
                }
            };

            function hydrate(e) {
                const s = "-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz";
                let t = 0;

                e = e.split(" ")[0];
                if (e)
                    for (let n = e.length, r = 0; r < n; r++)
                        t += s.indexOf(e[r]) * Math.pow(s.length, n - r - 1);

                let d = new Date(t);
                d.setTime(d.getTime() + 8 * 60 * 60 * 1000);

                return {
                    date: d,
                    str: d.toISOString().replace("T", " ").replace(/\..+/, "")
                };
            }

            function translate(e) {
                let m = e.message;
                
                let tags = m.match(/\$!\{player.+?\}/);
                if (tags !== null) {
                    tags.forEach(tag => {
                        let t = decodeURIComponent(tag);
                        t = JSON.parse(t.replace("$!{player:", "").replace(/\}$/, ""));
                        m = m.replace(tag, `【${t.n}<${t.t}>】`);
                    });
                }

                let tags2 = m.match(/\$!\{playerName:.+?\}/g);
                if (tags2 !== null) {
                    tags2.forEach(tag => {
                        let t = decodeURIComponent(tag);
                        t = t.replace(/\$\!\{playerName:(.+?)\}/g, "$1");
                        m = m.replace(tag, `【${t}】`);
                    });
                }

                m = `${hydrate(e.time).str} [${e.clanShortName ? e.clanShortName.toUpperCase() : "   "}] ${e.senderName}{${e.effectName ? e.effectName : "x"}} -> ${m}`;

                return m;
            }
        })();
    </script>
</body>

</html>