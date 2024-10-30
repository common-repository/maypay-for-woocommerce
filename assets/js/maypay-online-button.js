const mpbutton = document.createElement('template');


mpbutton.innerHTML = `
  <style>
    
    .MaypayButton .button{
        background: transparent linear-gradient(180deg, #00E091 0%, #00A2E0 100%) 0% 0% no-repeat padding-box;
        color: #fff;
        font-family: "__MPGiorgio", sans-serif;
        letter-spacing: 1.6px;
        font-size: 16px;
        text-align: center;
        padding: 12px;
        border-radius: 40px;
        border: none;
        -webkit-text-stroke: 0.7px #fff;
        box-shadow: 0px 15px 45px #00000029;
        cursor: pointer;
        overflow: hidden;
        position: relative;
    }
    .MaypayButton .button:hover{
        background: transparent linear-gradient(180deg, #0af4a2 0%, #00A2E0 100%) 0% 0% no-repeat padding-box;
    }

    .MaypayButton .button:after {
        content: "";
        position: absolute;
        width: 0px;
        height: 0px;
        top: 50%;
        left: 50%;
        transform-style: flat;
        transform: translate3d(-50%,-50%,0);
        background-color: #ffffff50;
        border-radius: 50%;
        transition: all .3s ease;
        padding-top: 200%;
        box-shadow: 5px 5px 40px #ffffff80;
        opacity: 0;
        
      }
      .MaypayButton .button:active:after {
        width: 200%;
        padding-top: 200%;
        opacity: 1;
      }
    
      .MaypayButton .button:disabled:active:after {
        width: 0px;
        padding-top: 50%;
        opacity: 0;
      }

    .MaypayButton .button .flex {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-evenly;
        min-width: 200px;
    }

    .MaypayButton .button .logoContainer {
        width: 44px;
        height: 44px;
    }

    .MaypayButton .button .logoContainer svg {
        width: 100%;
        height: 100%
    }
    
    .iframe{
        display: block;
        position: fixed;
        top:0;
        left:0;
        width: 100vw;
        height:100vh;
        overflow: hidden;
        z-index:10000;
        border: none;
        outline:none;
        background-color: #0000007f;
        backdrop-filter: blur(2px);
        background: linear-gradient(180deg, #00E091F2 0%, #00A2E0F2 100%) 0% 0% no-repeat padding-box;
    }
  </style>

  <div class="MaypayButton">
    <button class="button" disabled>
        <div class="flex">
            <div class="logoContainer">
            <?xml version="1.0" encoding="UTF-8" standalone="no"?>
            <svg
               xmlns:dc="http://purl.org/dc/elements/1.1/"
               xmlns:cc="http://creativecommons.org/ns#"
               xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
               xmlns:svg="http://www.w3.org/2000/svg"
               xmlns="http://www.w3.org/2000/svg"
               id="svg20"
               version="1.1"
               viewBox="0 0 63.999998 64.000001"
               height="64"
               width="64">
              <metadata
                 id="metadata26">
                <rdf:RDF>
                  <cc:Work
                     rdf:about="">
                    <dc:format>image/svg+xml</dc:format>
                    <dc:type
                       rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
                    <dc:title></dc:title>
                  </cc:Work>
                </rdf:RDF>
              </metadata>
              <defs
                 id="defs24" />
              <g
                 style="stroke-width:1.08252466"
                 transform="matrix(0.9237665,0,0,0.9237665,4.6241797,2.4184092)"
                 id="g4524">
                <path
                   d="m 48.135,12.362001 -9.381,7.576 a 17.182,17.182 0 0 1 6.392,13.473 c 0,9.166 -6.945,16.629 -15.508,16.705 -8.563,-0.076 -15.508,-7.539 -15.508,-16.705 a 17.182,17.182 0 0 1 6.392,-13.473 l -9.38,-7.576 A 28.632,28.632 0 0 0 0,35.013001 a 28.31,28.31 0 0 0 1.335,8.612 29.623,29.623 0 0 0 28.3,20.38 v 0 a 29.622,29.622 0 0 0 28.3,-20.38 28.31,28.31 0 0 0 1.335,-8.612 28.633,28.633 0 0 0 -11.135,-22.651 z"
                   id="path14"
                   style="fill:#ffffff;stroke-width:1.08252466" />
                <circle
                   cx="29.603001"
                   cy="5.9750013"
                   r="6.533"
                   id="circle16"
                   style="fill:#ffffff; stroke-width:1.08252466" />
              </g>
            </svg>
            
                
            </div>
            <div>
                Vinci o paga
            <div>
        </div>
    </button>
    <div class="frame"></div>
  </div>
`

class MaypayOnlineButton extends HTMLElement {
  constructor() {
    super();
    this._shadowRoot = this.attachShadow({ 'mode': 'closed' });
    this._shadowRoot.appendChild(mpbutton.content.cloneNode(true));
  }

  get paymentRequestId() {
    return this.getAttribute('paymentRequestId');
  }

  get callbackfn() {
    return this.getAttribute('callbackfn');
  }

  connectedCallback() {
    window.addEventListener("message", (event) => {
      if (event.origin === env.serviceUrl) {
        this.$frameContainer.innerHTML = "";
        window.dispatchEvent(new CustomEvent("maypay-event", {
          detail: event.data
        }))
      }
    }, false);
    console.log("connected", this.paymentRequestId);
    this.$button = this._shadowRoot.querySelector('.button');
    this.$frameContainer = this._shadowRoot.querySelector('.frame');
    this.$button.disabled = false;
    this.$button.onclick = () => {
      console.log("clicked")
      this.$frameContainer.innerHTML = `
                <iframe src="${env.serviceUrl}/${this.paymentRequestId}" class="iframe"></iframe> 
            `
    };

  }
}

window.customElements.define('maypay-online-button', MaypayOnlineButton);