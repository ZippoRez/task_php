import React from 'react';
import { Link } from 'react-router-dom'; //  Для создания ссылки на редактирование
import { TableRow, TableCell, Button, Typography } from '@mui/material'; //  Компоненты Material-UI
import { Account } from '../types/Account'; //  Тип данных Account
import { differenceInSeconds, formatDistanceToNow } from 'date-fns'; //  Для работы с датами

//  Интерфейс свойств компонента AccountRow
interface AccountRowProps {
  account: Account; //  Данные аккаунта
  onDelete: (id: number) => void; //  Функция,  вызываемая при нажатии на кнопку "Удалить"
  onRestore?: (id: number) => void; //  Функция,  вызываемая при нажатии на кнопку "Восстановить" (необязательная)
}

const AccountRow: React.FC<AccountRowProps> = ({ account, onDelete, onRestore }) => {
  //  Вычисляем время,  оставшееся до окончательного удаления аккаунта (1 час после deleted_at)
  const timeUntilDeletion = account.deleted_at 
    ? 1 * 60 * 60 - differenceInSeconds(new Date(), new Date(account.deleted_at)) 
    : null;

  //  JSX для отрисовки строки таблицы
  return (
    <TableRow key={account.id}>
      {/*  Ячейка для информации об удалении */}
      <TableCell>
        {account.deleted_at && ( //  Отображаем информацию,  только если аккаунт удален
          <Typography variant="caption" color="textSecondary"> 
            {/*  Отображаем,  когда аккаунт был удален */}
            Удалено {formatDistanceToNow(new Date(account.deleted_at), { addSuffix: true })} 
            {/*  Отображаем время,  оставшееся до окончательного удаления, если оно есть */}
            {timeUntilDeletion !== null && timeUntilDeletion > 0 && ( 
              <span>, остаётся: {formatDistanceToNow(new Date(Date.now() + timeUntilDeletion * 1000))}</span> 
            )}
          </Typography>
        )}
      </TableCell>

      {/*  Остальные ячейки с данными аккаунта */}
      <TableCell>{account.id}</TableCell>
      <TableCell>{account.first_name}</TableCell>
      <TableCell>{account.last_name}</TableCell>
      <TableCell>{account.email}</TableCell>
      <TableCell>{account.company_name}</TableCell>
      <TableCell>{account.position}</TableCell>
      <TableCell>{account.phone_1}</TableCell>
      <TableCell>{account.phone_2}</TableCell>
      <TableCell>{account.phone_3}</TableCell>

      {/*  Ячейка с кнопками действий */}
      <TableCell>
        {/*  Кнопка "Редактировать",  отображается только для неудаленных аккаунтов */}
        {!account.deleted_at && ( 
          <Button component={Link} to={`/edit/${account.id}`}>
            Редактировать
          </Button>
        )}

        {/*  Кнопка "Восстановить",  отображается только для удаленных аккаунтов,  
            если передана функция onRestore  */}
        {onRestore && account.deleted_at && ( 
          <Button onClick={() => onRestore(account.id)} color="success">
            Восстановить
          </Button>
        )}

        {/*  Кнопка "Удалить"  */}
        <Button onClick={() => onDelete(account.id)} color="error">
          Удалить
        </Button>
      </TableCell>
    </TableRow>
  );
};

export default AccountRow;