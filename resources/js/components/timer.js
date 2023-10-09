//Globals
let timerId;
const fields = {
  guys: document.querySelector('#guys'),
  current_guy: document.querySelector('#current_guy'),
  now: document.querySelector('#now'),
  started: document.querySelector('#started'),
  over: document.querySelector('#over'),
  end_time: document.querySelector('#end_time'),
  started: document.querySelector('#started'),
  remaining: document.querySelector('#remaining'),
  time_per_guy: document.querySelector('#time_per_guy'),
  current_guy_remaining: document.querySelector('#current_guy_remaining'),
  current_guy_over: document.querySelector('#current_guy_over'),
  message: document.querySelector('#message')
};

const token = document.querySelector('meta[name="csrf_token"]').content;
const underway = document.querySelector('#underway');
const notUnderway = document.querySelector('#notUnderway');
const runningInfo = document.querySelector('#runningInfo');
const startButton = document.querySelector('#startButton');
const stopButton = document.querySelector('#stopButton');
const resetButton = document.querySelector('#resetButton');
const passButton = document.querySelector('#passButton');
const warningButton = document.querySelector('#warningButton');
const warningAudio = document.querySelector('#warningAudio');
const alarmButton = document.querySelector('#alarmButton');
const alarmAudio = document.querySelector('#alarmAudio');

//Event handlers
if(startButton) {
  startButton.addEventListener('click',() => {
    startButton.classList.add('hidden');
    stopButton.classList.remove('hidden');
    passButton.classList.remove('bg-gray-200');
    passButton.classList.add('bg-blue-800');
    sendEvent('start');
  });
  stopButton.addEventListener('click',() => {
    startButton.classList.remove('hidden');
    stopButton.classList.add('hidden');
    passButton.classList.add('bg-gray-200');
    passButton.classList.remove('bg-blue-800');
    sendEvent('stop');
  });
  resetButton.addEventListener('click',() => {
    startButton.classList.remove('hidden');
    stopButton.classList.add('hidden');
    passButton.classList.add('bg-gray-200');
    passButton.classList.remove('bg-blue-800');
    sendEvent('reset');
  });
  passButton.addEventListener('click',() => {
    if(passButton.classList.contains('bg-gray-200')) {
      return;
    }
    sendEvent('pass');
  });
}

if(warningButton) {
  warningButton.addEventListener('click',() => {
    warningAudio.play();
  });
}

if(alarmButton) {
  alarmButton.addEventListener('click',() => {
    if(alarmAudio.duration > 0 && !alarmAudio.paused) {
      alarmAudio.pause();
    } else {
      alarmAudio.play();
    }
  });
}

const timerInit = () => {
  timerId = document.querySelector('#timerId').value;
  getTimerInfo(timerId);
};

const getTimerInfo = (timerId) => {
    const fetchPromise = fetch(`/timers/${timerId}/info`,{
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });
    fetchPromise.then(response => {
        if(response.ok) {
            return response.json();
        } else {
            console.log('The server responded with an error: ', response);
        }
    }).then(info => {
        console.log('info: ', info);
        updateTimerView(info);
        setTimeout(() => getTimerInfo(timerId),1000);

    }).catch(data => {
        console.log('Error: ', data);
    });
};

const updateTimerView = (info) => {
  const valuesToUpdate = [
    'guys',
    'current_guy',
    'now',
    'end_time',
    'remaining',
    'time_per_guy',
    'current_guy_remaining',
    'current_guy_over',
    'started',
    'over',
    'message',
  ];

  for(let el of valuesToUpdate) {
    if(!fields[el]) {
      continue;
    }
    if('started' === el) {
      if(String(info[el]) !== String(fields[el].value)) {
        toggleStartedState(info[el]);
        fields[el].value = info[el];
      }
    } else if('over' === el) {
      if(String(info[el]) !== String(fields[el].value)) {
        fields[el].value = info[el];
        if(info[el]) { //It's over
          fields.remaining.classList.add('bg-red-800');
          fields.remaining.classList.add('text-white');
        } else { //More time!
          fields.remaining.classList.remove('bg-red-800');
          fields.remaining.classList.remove('text-white');
        }
      }
    } else if('current_guy_over' === el) {
      if(String(info[el]) !== String(fields[el].value)) {
        fields[el].value = info[el];
        if(info[el]) { //It's over
          fields.current_guy_remaining.classList.add('bg-red-800');
          fields.current_guy_remaining.classList.add('text-white');
          fields.time_per_guy.classList.add('bg-red-800');
          fields.time_per_guy.classList.add('text-white');
        } else { //More time!
          fields.current_guy_remaining.classList.remove('bg-red-800');
          fields.current_guy_remaining.classList.remove('text-white');
          fields.time_per_guy.classList.remove('bg-red-800');
          fields.time_per_guy.classList.remove('text-white');
        }
      }
    } else if(String(info[el]) !== String(fields[el].innerHTML)) {
      fields[el].innerHTML = info[el];
    }
  }
  updateTimePerGuy(info);
};

const updateTimePerGuy = info => {

};

const toggleStartedState = startedState => {
  if(startedState) {
    notUnderway.classList.add('hidden');
    underway.classList.remove('hidden');
    runningInfo.classList.remove('hidden');
  } else {
    notUnderway.classList.remove('hidden');
    underway.classList.add('hidden');
    runningInfo.classList.add('hidden');
  }
};

const sendEvent = (eventName) => {
    const data = {
        timerId: timerId,
        eventName: eventName
    };
    console.log('data: ', data);
    const fetchPromise = fetch(`/timers/event`,{
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            "X-CSRF-Token": token
        },
        body: JSON.stringify({
            data: data
        }),
    });
    fetchPromise.then(response => {
        if(response.ok) {
            return response.json();
        } else {
            console.log('The server responded with an error: ', response);
        }
    }).then(data => {
        console.log(data);
    }).catch(data => {
        console.log('Error: ', data);
    });
};

document.addEventListener("DOMContentLoaded", () => {
  timerInit();
});
