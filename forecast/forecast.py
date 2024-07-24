from prophet import Prophet
import pandas as pd
import json
import sys

def forecast_data(data, periods, freq):
    # Process data into a DataFrame compatible with Prophet
    df = pd.DataFrame(data)
    df.columns = ['ds', 'y']  # Assuming 'ds' is datetime and 'y' is the value

    # Fit Prophet model
    model = Prophet()
    model.fit(df)

    # Make future predictions with the specified frequency
    future = model.make_future_dataframe(periods, freq=freq)
    forecast = model.predict(future)
    
    forecast['ds'] = forecast['ds'].dt.strftime('%Y-%m-%d %H:%M')

    return forecast[['ds', 'yhat']].tail(periods).to_json(orient='records')

def get_frequency(date_increment_type):
    # Map date_increment_type to Prophet frequency strings
    freq_mapping = {
        'seconds': 'S',
        'minutes': 'T',
        'hours': 'H',
        'days': 'D',
        'month': 'M'
    }
    return freq_mapping.get(date_increment_type, 'D')  # Default to 'D' for days if type is unknown

if __name__ == '__main__':
    # Read input data from PHP via stdin
    input_data = json.loads(sys.stdin.read())

    # Read periods and date_increment_type from command-line arguments
    periods = int(sys.argv[1])
    date_increment_type = sys.argv[2]  # Get date_increment_type from command-line arguments

    # Get the appropriate frequency for Prophet
    freq = get_frequency(date_increment_type)

    # Forecast data
    result = forecast_data(input_data, periods, freq)

    # Output forecast result
    print(result)
