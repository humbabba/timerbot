import './bootstrap';
import Alpine from 'alpinejs';
import './ajax-save';
import './trix-alignment';
import 'trix';
import { diffWords } from 'diff';

window.Alpine = Alpine;
window.diffWords = diffWords;

Alpine.start();
