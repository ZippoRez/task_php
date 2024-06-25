import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom'; // Для работы с маршрутизацией
import { 
  Button, Container, Typography 
} from '@mui/material'; // Компоненты Material-UI
import { Account } from '../types/Account'; // Тип данных Account
import config from '../config'; // Конфигурация приложения
import AccountForm from './AccountForm'; // Компонент формы редактирования аккаунта

// Компонент для редактирования аккаунта
const AccountEdit: React.FC = () => {
  // Получаем ID аккаунта из параметров URL
  const { id } = useParams<{ id: string }>();
  // Хук для перенаправления
  const navigate = useNavigate();
  // Преобразуем ID в число, если он есть, иначе 0
  const accountId = id ? parseInt(id, 10) : 0;

  // Состояние для хранения данных формы, инициализируем пустым объектом Account
  const [formData, setFormData] = useState<Account>({
    id: 0,
    first_name: '',
    last_name: '',
    email: '',
    company_name: '',
    position: '',
    phone_1: '',
    phone_2: '',
    phone_3: '',
    deleted_at: '',
  });
  // Состояние для индикации загрузки данных: true - данные загружаются, false - данные загружены
  const [loading, setLoading] = useState(true);
  // Состояние для хранения сообщения об ошибке
  const [error, setError] = useState<string | null>(null);

  // useEffect для загрузки данных аккаунта при монтировании компонента и при каждом изменении accountId
  useEffect(() => {
    // Асинхронная функция для загрузки данных
    const fetchData = async () => {
      try {
        // Отправляем GET запрос на сервер для получения данных аккаунта по ID
        const response = await fetch(`${config.apiUrl}/index.php?id=${accountId}`, {
          method: 'GET',
        });

        // Проверяем статус ответа
        if (!response.ok) {
          // Если ошибка, генерируем исключение
          throw new Error('Ошибка при загрузке данных аккаунта'); 
        }

        // Парсим ответ сервера в JSON
        const data = await response.json();

        // Проверяем, что полученные данные имеют корректный формат (объект с полем data)
        if (typeof data.data === 'object' && data.data !== null) {
          // Если формат корректен, устанавливаем данные в состояние formData
          setFormData(data.data);
        } else {
          // Если данные некорректны, генерируем ошибку
          throw new Error('Неверный формат данных аккаунта'); 
        }
      } catch (err) {
        // Обрабатываем ошибки (загрузки данных или неверного формата)
        // Устанавливаем сообщение об ошибке в состояние error
        setError('Ошибка при загрузке данных аккаунта');
        // Выводим ошибку в консоль
        console.error(err); 
      } finally {
        // Скрываем индикатор загрузки в любом случае (успех или ошибка)
        setLoading(false); 
      }
    };

    // Вызываем функцию fetchData
    fetchData();
  }, [accountId]); // Зависимость от accountId - useEffect будет срабатывать при каждом его изменении

  // Обработчик изменения значения в поле формы
  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    // Обновляем состояние formData
    setFormData({
      ...formData, // Копируем текущие данные formData
      [event.target.name]: event.target.value, // Обновляем значение поля, имя которого совпадает с 'name' инпута
    });
  };

  // Обработчик отправки формы
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault(); // Предотвращаем стандартную отправку формы браузером
    setError(null); // Сбрасываем сообщение об ошибке

    try {
      // Отправляем PUT запрос на сервер для обновления аккаунта
      const response = await fetch(`${config.apiUrl}/edit.php?id=${accountId}`, {
        method: 'PUT', // Метод запроса - PUT
        headers: { 'Content-Type': 'application/json' }, // Указываем, что отправляем данные в формате JSON
        body: JSON.stringify(formData), // Преобразуем данные формы в JSON
      });

      // Проверяем статус ответа
      if (!response.ok) {
        // Если ошибка, парсим ответ сервера в JSON
        const errorData = await response.json();
        // Генерируем исключение с сообщением об ошибке
        throw new Error(errorData.error || 'Ошибка при обновлении аккаунта'); 
      }

      // Если обновление успешно, перенаправляем пользователя на главную страницу
      navigate('/'); 
    } catch (err) {
      // Обрабатываем исключения (ошибки сети или сервера)
      setError((err as Error).message); // Устанавливаем сообщение об ошибке в состояние error
    }
  };

  // Если данные загружаются (loading === true), отображаем сообщение "Загрузка данных..."
  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>; 
  }

  // Если произошла ошибка при загрузке данных (error !== null), отображаем сообщение об ошибке
  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>; 
  }

  // Если данные загружены и нет ошибок, отображаем форму редактирования
  return (
    <Container maxWidth="sm"> 
      {/* Заголовок */}
      <Typography variant="h4" align="center" gutterBottom>
        {/* Кнопка "Назад", ведущая на главную страницу */}
        <Button component={Link} to="/" variant="contained" color="primary" sx={{float:'left'}}>
          ←
        </Button>
        Редактировать аккаунт
      </Typography>

      {/* Вывод сообщения об ошибке (дублируется? Скорее всего, ошибка) */}
      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      {/* Отрисовка формы редактирования аккаунта */}
      {/* Передаем в компонент AccountForm данные аккаунта, обработчик изменения полей и обработчик отправки формы  */}
      <AccountForm formData={formData} onChange={handleChange} onSubmit={handleSubmit} /> 
    </Container>
  );
};

export default AccountEdit;