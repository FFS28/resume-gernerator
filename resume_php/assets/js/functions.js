function minToHour(minutes, dense = false, showAll = false){
  let d = Number(minutes);
  if (d === 0) {
    return '';
  }

  let h = Math.floor(d / 60);
  let m = Math.floor(d % 60);

  if (dense) {
    let hDisplay = h;
    let mDisplay = (m < 10 ? '0' : '') + m;
    return (h > 0 || showAll ? hDisplay : '') + (h > 0 || showAll ? ":" : "") + (h > 0 || showAll ? mDisplay : m);
  } else {
    let hDisplay = h > 0 ? h + " h" : "";
    let mDisplay = m > 0 ? m + " min" : "";
    return hDisplay + (h > 0 && m > 0 ? " " : "") + mDisplay;
  }
}

function normalize(str) {
  str = str.toLowerCase();
  str = str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  return str;
}

function convert(value, isLiquid) {
  return value + ' ' +  (isLiquid ? 'ml' : 'g');
}

export default {minToHour, normalize, convert};
