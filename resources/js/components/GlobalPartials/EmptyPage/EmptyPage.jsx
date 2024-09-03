import { Button, Icon, Page, Text } from '@shopify/polaris'
import React, { useCallback, useState } from 'react'
import PlayCircleMajor from '../../../../images/noPlan.jpg'
import { useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { changeIndex, resetPlanData, resetPlanErrors } from '../../../data/features/plansDetails/plansDetailsSlice';
import { storeReducer } from '../../../data/features/plans/plansSlice';
import { getRestricatedContents } from '../../../data/features/plansDetails/planAction';

export default function EmptyPage({showButton}) {

    const navigate = useNavigate();
    const dispatch = useDispatch();

    const newStore$ = useSelector((state) => state.plans?.newStore);
    const plansDetail$ = useSelector((state) => state.plansDetail);

    const [play, setPlay] = useState(false);

    const handlePlay = useCallback(() => {
        setPlay(true);
    }, [play])

    const createPlan = useCallback(() => {
        dispatch(storeReducer(true));
        dispatch(resetPlanData());
        dispatch(resetPlanErrors());
        dispatch(changeIndex(0));
        dispatch(getRestricatedContents());
        navigate('/plans/new');
    }, [newStore$, plansDetail$])

  return (
      <Page>
          <div className='empty_page_block'>

              {/* video */}
              <div className='video_block'>
                  {

                      play ?
                      <iframe width="532" height="300" src="https://www.youtube.com/embed/HDUBmkKi0Hg?loop=1" allow="autoplay"></iframe>
                      :  <img src={PlayCircleMajor} alt='PlayCircleMajor' onClick={handlePlay} />
                  }

              </div>

              <div className='empty_content_block'>
                  <Text variant="headingXl" as="h4" fontWeight='medium'> Create your first plan </Text>
                  <div className='empty_note_wrap'>
                      <Text variant="bodyLg" as="h6" fontWeight='regular'>Plans are rules for how memberships are sold on your store. They also determine which benefits members get when they subscribe to these plans. Create a plan to choose your membership products, and to set the price and membership length of your memberships. Watch the video above for more information.</Text>
                  </div>

                  {showButton && <Button  onClick={() => createPlan()} variant="primary">Create Plan</Button> }
              </div>
          </div>
      </Page>
  );
}
